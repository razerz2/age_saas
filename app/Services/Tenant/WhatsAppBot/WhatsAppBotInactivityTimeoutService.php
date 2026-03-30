<?php

namespace App\Services\Tenant\WhatsAppBot;

use App\Models\Tenant\WhatsAppBotSession;
use App\Services\Tenant\WhatsAppBot\DTO\OutboundMessage;
use App\Services\Tenant\WhatsAppBot\Provider\Contracts\WhatsAppBotProviderAdapterInterface;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderAdapterFactory;
use App\Services\Tenant\WhatsAppBot\Provider\WhatsAppBotProviderResolver;
use App\Services\Tenant\WhatsAppBotConfigService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppBotInactivityTimeoutService
{
    private const MAX_BATCH_SIZE = 500;
    private const RETRY_BACKOFF_SECONDS = 300;

    public function __construct(
        private readonly WhatsAppBotConfigService $configService,
        private readonly WhatsAppBotProviderResolver $providerResolver,
        private readonly WhatsAppBotProviderAdapterFactory $adapterFactory
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function sweepCurrentTenant(
        bool $dryRun = false,
        ?CarbonInterface $now = null,
        ?string $tenantIdOverride = null
    ): array
    {
        $nowAt = $now ? Carbon::instance($now->toMutable()) : now();
        $settings = $this->configService->getSettings();
        $tenantId = $tenantIdOverride !== null && trim($tenantIdOverride) !== ''
            ? trim($tenantIdOverride)
            : $this->currentTenantId();

        $stats = [
            'tenant_id' => $tenantId,
            'enabled' => (bool) data_get($settings, 'enabled', false),
            'end_on_inactivity' => (bool) data_get($settings, 'session.end_on_inactivity', true),
            'idle_timeout_minutes' => max(1, (int) data_get($settings, 'session.idle_timeout_minutes', 30)),
            'dry_run' => $dryRun,
            'candidates' => 0,
            'processed' => 0,
            'sent' => 0,
            'failed_send' => 0,
            'skipped' => 0,
            'retry_scheduled' => 0,
        ];

        if (!$stats['enabled']) {
            $stats['reason'] = 'bot_disabled';
            return $stats;
        }

        if (!$stats['end_on_inactivity']) {
            $stats['reason'] = 'end_on_inactivity_disabled';
            return $stats;
        }

        $sessions = $this->findExpiredActiveSessions(
            idleTimeoutMinutes: $stats['idle_timeout_minutes'],
            now: $nowAt,
            tenantId: $tenantId
        );
        $stats['candidates'] = $sessions->count();

        if ($sessions->isEmpty()) {
            Log::info('whatsapp_bot.inactivity.sweep_finished', $stats);
            return $stats;
        }

        $adapter = $this->resolveAdapterForCurrentTenant();

        foreach ($sessions as $session) {
            $result = $this->processSession(
                session: $session,
                settings: $settings,
                adapter: $adapter,
                now: $nowAt,
                dryRun: $dryRun,
                persist: !$dryRun
            );

            if (($result['processed'] ?? false) === true) {
                $stats['processed']++;
            } else {
                $stats['skipped']++;
            }

            if (($result['sent'] ?? false) === true) {
                $stats['sent']++;
            }

            if (($result['send_failed'] ?? false) === true) {
                $stats['failed_send']++;
            }

            if (($result['retry_scheduled'] ?? false) === true) {
                $stats['retry_scheduled']++;
            }

            Log::info('whatsapp_bot.inactivity.session_processed', $result);
        }

        Log::info('whatsapp_bot.inactivity.sweep_finished', $stats);

        return $stats;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function processSession(
        WhatsAppBotSession $session,
        array $settings,
        ?WhatsAppBotProviderAdapterInterface $adapter = null,
        ?CarbonInterface $now = null,
        bool $dryRun = false,
        bool $persist = true
    ): array {
        $nowAt = $now ? Carbon::instance($now->toMutable()) : now();
        $statusBefore = trim(strtolower((string) ($session->status ?? '')));
        $referenceAt = $this->resolveIdleReferenceAt($session);

        $result = [
            'session_id' => (string) ($session->id ?? ''),
            'tenant_id' => $this->currentTenantId(),
            'phone' => trim((string) ($session->contact_phone ?? '')),
            'status_before' => $statusBefore,
            'status_after' => $statusBefore,
            'last_inbound_message_at' => $session->last_inbound_message_at?->toDateTimeString(),
            'idle_reference_at' => $referenceAt?->toDateTimeString(),
            'dry_run' => $dryRun,
            'processed' => false,
            'sent' => false,
            'send_failed' => false,
            'retry_scheduled' => false,
            'reason' => null,
            'error' => null,
        ];

        if (!(bool) data_get($settings, 'session.end_on_inactivity', true)) {
            $result['reason'] = 'end_on_inactivity_disabled';
            return $result;
        }

        if ($statusBefore !== 'active') {
            $result['reason'] = 'session_not_active';
            return $result;
        }

        $idleTimeoutMinutes = max(1, (int) data_get($settings, 'session.idle_timeout_minutes', 30));
        if (!$this->isSessionIdleExpired($session, $idleTimeoutMinutes, $nowAt)) {
            $result['reason'] = 'idle_timeout_not_reached';
            return $result;
        }

        if ($this->isRetryWindowActive($session, $nowAt)) {
            $result['reason'] = 'retry_window_active';
            return $result;
        }

        if ($this->alreadyHandledByInactivity($session)) {
            $result['reason'] = 'already_handled';
            return $result;
        }

        if ($dryRun) {
            $result['processed'] = true;
            $result['reason'] = 'dry_run_expired_session';
            return $result;
        }

        $message = trim((string) data_get(
            $settings,
            'messages.inactivity_exit',
            WhatsAppBotConfigService::DEFAULT_INACTIVITY_EXIT_MESSAGE
        ));

        $sendOutcome = $this->sendInactivityMessage($adapter, $session, $message, $nowAt);
        if (!$sendOutcome['sent']) {
            $result['processed'] = true;
            $result['send_failed'] = true;
            $result['retry_scheduled'] = true;
            $result['reason'] = 'inactivity_timeout_send_failed';
            $result['error'] = (string) ($sendOutcome['error'] ?? 'provider_send_failed');

            $session->meta = $this->markSendFailure(
                meta: is_array($session->meta) ? $session->meta : [],
                now: $nowAt,
                error: $result['error']
            );

            if ($persist) {
                $session->save();
            }

            return $result;
        }

        $state = is_array($session->state) ? $session->state : [];
        $state = $this->applySessionTimeoutState($state, $settings, $nowAt);

        $meta = $this->markSendSuccess(
            meta: is_array($session->meta) ? $session->meta : [],
            now: $nowAt,
            referenceAt: $referenceAt
        );
        $meta['last_end_reason'] = 'inactivity_timeout';
        $meta['last_end_at'] = $nowAt->toDateTimeString();
        $meta['inactivity_auto_closed_at'] = $nowAt->toDateTimeString();

        $meta['inactivity_reference_at'] = $referenceAt?->toDateTimeString();

        $meta['inactivity_notified_at'] = $nowAt->toDateTimeString();
        $meta['inactivity_notification_sent'] = true;

        $result['sent'] = true;
        $result['processed'] = true;
        $result['reason'] = 'inactivity_timeout_closed';

        $session->status = 'ended';
        $result['status_after'] = 'ended';
        $session->current_flow = 'menu';
        $session->current_step = 'menu.awaiting_option';
        $session->state = $state;
        $session->meta = $meta;
        $session->last_outbound_message_at = $nowAt;

        if ($persist) {
            $session->save();
        }

        return $result;
    }

    private function isSessionIdleExpired(WhatsAppBotSession $session, int $idleTimeoutMinutes, CarbonInterface $now): bool
    {
        $reference = $this->resolveIdleReferenceAt($session);
        if (!$reference instanceof CarbonInterface) {
            return false;
        }

        return $reference->lessThanOrEqualTo($now->copy()->subMinutes($idleTimeoutMinutes));
    }

    private function resolveIdleReferenceAt(WhatsAppBotSession $session): ?CarbonInterface
    {
        if ($session->last_inbound_message_at instanceof CarbonInterface) {
            return $session->last_inbound_message_at;
        }

        if ($session->updated_at instanceof CarbonInterface) {
            return $session->updated_at;
        }

        return $session->created_at instanceof CarbonInterface
            ? $session->created_at
            : null;
    }

    /**
     * @return Collection<int, WhatsAppBotSession>
     */
    private function findExpiredActiveSessions(int $idleTimeoutMinutes, CarbonInterface $now, ?string $tenantId = null): Collection
    {
        $threshold = $now->copy()->subMinutes($idleTimeoutMinutes);
        $query = WhatsAppBotSession::query()
            ->where('channel', 'whatsapp')
            ->where('status', 'active')
            ->orderBy('last_inbound_message_at')
            ->orderBy('updated_at')
            ->orderBy('created_at')
            ->limit(self::MAX_BATCH_SIZE);

        if ($tenantId !== null && $tenantId !== '') {
            $query->where('tenant_id', $tenantId);
        }

        $active = $query->get();
        if ($active->isEmpty()) {
            Log::info('whatsapp_bot.inactivity.candidate_filters', [
                'tenant_id' => $tenantId,
                'threshold' => $threshold->toDateTimeString(),
                'active_pool' => 0,
                'raw_expired_active' => 0,
                'after_dedup' => 0,
                'blocked_idle_timeout' => 0,
                'blocked_retry_window' => 0,
                'blocked_already_handled' => 0,
                'final_candidates' => 0,
            ]);

            return $active;
        }

        $rawExpired = collect();
        $blockedIdle = 0;

        foreach ($active as $session) {
            if (!$this->isSessionIdleExpired($session, $idleTimeoutMinutes, $now)) {
                $blockedIdle++;
                $this->logCandidateDecision($session, $threshold, 'excluded', 'idle_timeout_not_reached', [
                    'filter' => 'idle_timeout',
                ]);
                continue;
            }

            $rawExpired->push($session);
        }

        $dedupResult = $this->deduplicateByContactPhone($rawExpired);
        $deduped = $dedupResult['deduped'];
        /** @var Collection<int, WhatsAppBotSession> $droppedByDedup */
        $droppedByDedup = $dedupResult['dropped'];

        foreach ($droppedByDedup as $duplicateSession) {
            $this->logCandidateDecision($duplicateSession, $threshold, 'excluded', 'duplicate_phone_deduped', [
                'filter' => 'dedup_phone',
            ]);
        }

        $blockedRetry = 0;
        $blockedHandled = 0;
        $final = collect();

        foreach ($deduped as $session) {
            if ($this->isRetryWindowActive($session, $now)) {
                $blockedRetry++;
                $this->logCandidateDecision($session, $threshold, 'excluded', 'retry_window_active', [
                    'filter' => 'retry_window',
                ]);
                continue;
            }

            if ($this->alreadyHandledByInactivity($session)) {
                $blockedHandled++;
                $this->logCandidateDecision($session, $threshold, 'excluded', 'already_handled_by_inactivity', [
                    'filter' => 'already_handled',
                ]);
                continue;
            }

            $this->logCandidateDecision($session, $threshold, 'included', 'eligible_for_inactivity_expiration', [
                'filter' => 'final',
            ]);
            $final->push($session);
        }

        Log::info('whatsapp_bot.inactivity.candidate_filters', [
            'tenant_id' => $tenantId,
            'threshold' => $threshold->toDateTimeString(),
            'active_pool' => $active->count(),
            'raw_expired_active' => $rawExpired->count(),
            'after_dedup' => $deduped->count(),
            'blocked_idle_timeout' => $blockedIdle,
            'blocked_retry_window' => $blockedRetry,
            'blocked_already_handled' => $blockedHandled,
            'final_candidates' => $final->count(),
        ]);

        return $final->values();
    }

    private function resolveAdapterForCurrentTenant(): ?WhatsAppBotProviderAdapterInterface
    {
        try {
            $resolved = $this->providerResolver->resolveForCurrentTenant(requireEnabled: false);
            if (!(bool) ($resolved['enabled'] ?? false)) {
                return null;
            }

            $provider = (string) ($resolved['provider'] ?? '');
            if (!$this->adapterFactory->isSupported($provider)) {
                return null;
            }

            $this->providerResolver->applyRuntimeConfig($resolved);

            return $this->adapterFactory->make($provider);
        } catch (\Throwable $exception) {
            Log::warning('whatsapp_bot.inactivity.adapter_unavailable', [
                'tenant_id' => (string) (tenant()?->id ?? ''),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function applySessionTimeoutState(array $state, array $settings, CarbonInterface $now): array
    {
        $clearContextOnEnd = (bool) data_get($settings, 'session.clear_context_on_end', true);
        $allowResumePrevious = (bool) data_get($settings, 'session.allow_resume_previous', false);

        if ($clearContextOnEnd || !$allowResumePrevious) {
            $state['schedule'] = [];
            $state['cancel'] = [];
            unset($state['registration'], $state['pending_intent'], $state['patient_id'], $state['patient_name']);
        }

        $meta = is_array($state['_meta'] ?? null) ? $state['_meta'] : [];
        $meta['invalid_attempts'] = 0;
        $meta['last_end_reason'] = 'inactivity_timeout';
        $meta['last_end_at'] = $now->toDateTimeString();
        $meta['session_started_at'] = $now->toDateTimeString();
        $state['_meta'] = $meta;

        return $state;
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function markSendFailure(array $meta, CarbonInterface $now, string $error): array
    {
        $inactivity = is_array($meta['inactivity_timeout'] ?? null)
            ? $meta['inactivity_timeout']
            : [];

        $attempts = (int) ($inactivity['send_attempts'] ?? 0) + 1;
        $inactivity['status'] = 'send_failed';
        $inactivity['send_attempts'] = $attempts;
        $inactivity['last_attempt_at'] = $now->toDateTimeString();
        $inactivity['next_retry_at'] = $now->copy()->addSeconds(self::RETRY_BACKOFF_SECONDS)->toDateTimeString();
        $inactivity['last_error'] = $error;

        $meta['inactivity_timeout'] = $inactivity;
        $meta['inactivity_notification_sent'] = false;
        $meta['inactivity_last_error'] = $error;
        $meta['inactivity_last_attempt_at'] = $now->toDateTimeString();

        return $meta;
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function markSendSuccess(array $meta, CarbonInterface $now, ?CarbonInterface $referenceAt): array
    {
        $inactivity = is_array($meta['inactivity_timeout'] ?? null)
            ? $meta['inactivity_timeout']
            : [];

        $inactivity['status'] = 'sent';
        $inactivity['send_attempts'] = (int) ($inactivity['send_attempts'] ?? 0) + 1;
        $inactivity['last_attempt_at'] = $now->toDateTimeString();
        $inactivity['sent_at'] = $now->toDateTimeString();
        $inactivity['closed_at'] = $now->toDateTimeString();
        $inactivity['next_retry_at'] = null;
        $inactivity['last_error'] = null;
        $inactivity['idle_reference_at'] = $referenceAt?->toDateTimeString();

        $meta['inactivity_timeout'] = $inactivity;

        return $meta;
    }

    /**
     * @return array{sent: bool, error: string|null}
     */
    private function sendInactivityMessage(
        ?WhatsAppBotProviderAdapterInterface $adapter,
        WhatsAppBotSession $session,
        string $message,
        CarbonInterface $now
    ): array {
        $to = trim((string) ($session->contact_phone ?? ''));
        if ($to === '') {
            return ['sent' => false, 'error' => 'missing_contact_phone'];
        }

        if (trim($message) === '') {
            return ['sent' => false, 'error' => 'empty_inactivity_message'];
        }

        if (!$adapter instanceof WhatsAppBotProviderAdapterInterface) {
            return ['sent' => false, 'error' => 'adapter_unavailable'];
        }

        try {
            $sent = $adapter->sendOutbound(OutboundMessage::text(
                to: $to,
                text: $message,
                meta: [
                    'kind' => 'inactivity_timeout_auto',
                    'session_id' => (string) ($session->id ?? ''),
                    'ended_at' => $now->toDateTimeString(),
                ]
            ));

            return $sent
                ? ['sent' => true, 'error' => null]
                : ['sent' => false, 'error' => 'provider_send_failed'];
        } catch (Throwable $exception) {
            return ['sent' => false, 'error' => $exception->getMessage()];
        }
    }

    private function currentTenantId(): ?string
    {
        if (!function_exists('tenant')) {
            return null;
        }

        try {
            $tenant = tenant();
            if (!$tenant || !isset($tenant->id)) {
                return null;
            }

            $id = trim((string) $tenant->id);
            return $id !== '' ? $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param Collection<int, WhatsAppBotSession> $sessions
     * @return Collection<int, WhatsAppBotSession>
     */
    /**
     * @param Collection<int, WhatsAppBotSession> $sessions
     * @return array{
     *   deduped: Collection<int, WhatsAppBotSession>,
     *   dropped: Collection<int, WhatsAppBotSession>
     * }
     */
    private function deduplicateByContactPhone(Collection $sessions): array
    {
        $sorted = $sessions
            ->sortByDesc(function (WhatsAppBotSession $session): int {
                $reference = $this->resolveIdleReferenceAt($session);
                return $reference?->getTimestamp() ?? 0;
            });

        $grouped = $sorted->groupBy(function (WhatsAppBotSession $session): string {
            $phone = trim((string) ($session->contact_phone ?? ''));
            return $phone !== '' ? $phone : (string) ($session->id ?? '');
        });

        $duplicates = $grouped
            ->filter(static fn (Collection $items): bool => $items->count() > 1)
            ->map(static fn (Collection $items): array => $items->map(
                static fn (WhatsAppBotSession $session): string => (string) ($session->id ?? '')
            )->values()->all())
            ->all();

        if ($duplicates !== []) {
            Log::warning('whatsapp_bot.inactivity.duplicate_sessions_detected', [
                'tenant_id' => $this->currentTenantId(),
                'duplicates' => $duplicates,
            ]);
        }

        $deduped = $sorted
            ->unique(function (WhatsAppBotSession $session): string {
                $phone = trim((string) ($session->contact_phone ?? ''));
                return $phone !== '' ? $phone : (string) ($session->id ?? '');
            })
            ->values();

        $dedupedIds = $deduped->map(static fn (WhatsAppBotSession $session): string => (string) ($session->id ?? ''))
            ->filter()
            ->values();

        $dropped = $sorted->filter(function (WhatsAppBotSession $session) use ($dedupedIds): bool {
            $id = (string) ($session->id ?? '');
            return $id !== '' && !$dedupedIds->contains($id);
        })->values();

        return [
            'deduped' => $deduped,
            'dropped' => $dropped,
        ];
    }

    private function isRetryWindowActive(WhatsAppBotSession $session, CarbonInterface $now): bool
    {
        $meta = is_array($session->meta) ? $session->meta : [];
        $inactivity = is_array($meta['inactivity_timeout'] ?? null)
            ? $meta['inactivity_timeout']
            : [];

        $nextRetryAtRaw = trim((string) ($inactivity['next_retry_at'] ?? ''));
        if ($nextRetryAtRaw === '') {
            return false;
        }

        try {
            $nextRetryAt = Carbon::parse($nextRetryAtRaw);
        } catch (Throwable) {
            return false;
        }

        return $nextRetryAt->gt($now);
    }

    private function alreadyHandledByInactivity(WhatsAppBotSession $session): bool
    {
        $meta = is_array($session->meta) ? $session->meta : [];
        $inactivity = is_array($meta['inactivity_timeout'] ?? null)
            ? $meta['inactivity_timeout']
            : [];

        $sentAtRaw = trim((string) ($inactivity['sent_at'] ?? $inactivity['closed_at'] ?? ''));
        if ($sentAtRaw === '') {
            return false;
        }

        try {
            $sentAt = Carbon::parse($sentAtRaw);
        } catch (Throwable) {
            return false;
        }

        $reference = $this->resolveIdleReferenceAt($session);
        if (!$reference instanceof CarbonInterface) {
            return true;
        }

        // If there was no inbound activity after the last inactivity closure, skip reprocessing.
        return $reference->lessThanOrEqualTo($sentAt);
    }

    private function logCandidateDecision(
        WhatsAppBotSession $session,
        CarbonInterface $threshold,
        string $decision,
        string $reason,
        array $extra = []
    ): void {
        $reference = $this->resolveIdleReferenceAt($session);

        Log::info('whatsapp_bot.inactivity.candidate_decision', array_merge([
            'tenant_id' => $this->currentTenantId(),
            'session_id' => (string) ($session->id ?? ''),
            'phone' => (string) ($session->contact_phone ?? ''),
            'status' => (string) ($session->status ?? ''),
            'channel' => (string) ($session->channel ?? ''),
            'current_flow' => (string) ($session->current_flow ?? ''),
            'current_step' => (string) ($session->current_step ?? ''),
            'idle_reference_at' => $reference?->toDateTimeString(),
            'threshold' => $threshold->toDateTimeString(),
            'decision' => $decision,
            'reason' => $reason,
        ], $extra));
    }
}
