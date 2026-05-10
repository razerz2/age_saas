<?php

namespace App\Services\Tenant\OnlineMeetings;

use App\Contracts\Tenant\OnlineMeetingProviderInterface;
use App\DTO\Tenant\OnlineMeetingResult;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Tenant\TenantSetting;
use App\Support\Tenant\OnlineMeeting;
use Illuminate\Support\Facades\Log;
use Throwable;

class OnlineMeetingManager
{
    public function __construct(
        private readonly ?ManualMeetingProvider $manualMeetingProvider = null,
        private readonly ?GoogleMeetProvider $googleMeetProvider = null
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function provisionFor(Appointment $appointment, array $options = []): OnlineMeetingResult
    {
        $this->loadProvisionContext($appointment);

        if (!$this->shouldHandle($appointment)) {
            Log::info('OnlineMeetingManager provision skipped: appointment not online/valid', [
                'appointment_id' => $appointment->id ?? null,
            ]);

            return OnlineMeetingResult::skipped(
                errorMessage: 'Appointment is not eligible for online meeting handling.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        if (!$this->shouldProvisionNow($appointment, $options)) {
            $timingForLog = $this->timingFromOptions($options, false);

            Log::info('OnlineMeetingManager provision skipped by auto-generate/timing rules', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'timing' => $timingForLog,
            ]);

            return OnlineMeetingResult::skipped(
                provider: $this->resolveProviderKeyForProvision($options),
                errorMessage: 'Online meeting generation is not allowed for this appointment state.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'status' => $appointment->status,
                    'timing' => $timingForLog,
                ]
            );
        }

        $instruction = $this->instructionFrom($appointment);
        if ($this->hasGeneratedMeeting($instruction)) {
            Log::info('OnlineMeetingManager provision idempotent success: meeting already generated', [
                'appointment_id' => $appointment->id,
                'provider' => $instruction?->meeting_provider,
            ]);

            return OnlineMeetingResult::success(
                provider: $instruction?->meeting_provider ?: $this->resolveProviderKeyForProvision($options),
                meetingLink: $instruction?->meeting_link,
                externalEventId: $instruction?->external_event_id,
                externalMeetingId: $instruction?->external_meeting_id,
                meta: [
                    'appointment_id' => $appointment->id,
                    'idempotent' => true,
                ]
            );
        }

        $providerKey = $this->resolveProviderKeyForProvision($options);
        $provider = $this->providerFor($providerKey);

        $providerKeySafe = $this->safeProviderKey($provider, $providerKey);

        Log::info('OnlineMeetingManager provision started', [
            'appointment_id' => $appointment->id,
            'provider' => $providerKeySafe,
        ]);

        try {
            $result = $provider->createForAppointment($appointment);

            Log::info('OnlineMeetingManager provision finished', [
                'appointment_id' => $appointment->id,
                'provider' => $providerKeySafe,
                'result_status' => $result->status,
                'result_success' => $result->success,
            ]);

            return $result;
        } catch (Throwable $e) {
            Log::error('OnlineMeetingManager provision failed with exception', [
                'appointment_id' => $appointment->id,
                'provider' => $providerKeySafe,
                'error' => $e->getMessage(),
            ]);

            return OnlineMeetingResult::failed(
                provider: $providerKeySafe,
                errorMessage: 'Failed to provision online meeting.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function updateFor(Appointment $appointment, array $options = []): OnlineMeetingResult
    {
        if ($appointment->exists) {
            $appointment->loadMissing(['onlineInstructions']);
        }

        if (!$this->shouldHandle($appointment)) {
            Log::info('OnlineMeetingManager update skipped: appointment not online/valid', [
                'appointment_id' => $appointment->id ?? null,
            ]);

            return OnlineMeetingResult::skipped(
                errorMessage: 'Appointment is not eligible for online meeting update.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $force = $this->flag($options, 'force');
        $instruction = $this->instructionFrom($appointment);
        if ($instruction && $instruction->meeting_status === OnlineMeeting::STATUS_MANUAL_REQUIRED && !$force) {
            Log::info('OnlineMeetingManager update skipped: manual_required without force', [
                'appointment_id' => $appointment->id,
                'provider' => $instruction->meeting_provider,
            ]);

            return OnlineMeetingResult::skipped(
                provider: $instruction->meeting_provider ?: $this->defaultProvider(),
                errorMessage: 'Meeting is marked as manual_required. Use force to retry provider update.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        if (!$this->hasGeneratedMeeting($instruction)) {
            $provisionOptions = $options;
            if (!isset($provisionOptions['provider']) && $instruction && filled($instruction->meeting_provider)) {
                $provisionOptions['provider'] = $instruction->meeting_provider;
            }

            return $this->provisionFor($appointment, $provisionOptions);
        }

        $providerKey = $instruction?->meeting_provider ?: ($options['provider'] ?? $this->defaultProvider());
        $provider = $this->providerFor(is_string($providerKey) ? $providerKey : null);

        $providerKeySafe = $this->safeProviderKey($provider, is_string($providerKey) ? $providerKey : null);

        Log::info('OnlineMeetingManager update started', [
            'appointment_id' => $appointment->id,
            'provider' => $providerKeySafe,
        ]);

        try {
            $result = $provider->updateForAppointment($appointment);

            Log::info('OnlineMeetingManager update finished', [
                'appointment_id' => $appointment->id,
                'provider' => $providerKeySafe,
                'result_status' => $result->status,
                'result_success' => $result->success,
            ]);

            return $result;
        } catch (Throwable $e) {
            Log::error('OnlineMeetingManager update failed with exception', [
                'appointment_id' => $appointment->id,
                'provider' => $providerKeySafe,
                'error' => $e->getMessage(),
            ]);

            return OnlineMeetingResult::failed(
                provider: $providerKeySafe,
                errorMessage: 'Failed to update online meeting.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function cancelFor(Appointment $appointment, array $options = []): OnlineMeetingResult
    {
        if ($appointment->exists) {
            $appointment->loadMissing(['onlineInstructions']);
        }
        $instruction = $this->instructionFrom($appointment);
        $force = $this->flag($options, 'force');

        $canHandle = $this->shouldHandle($appointment);
        $hasCancelableInstruction = $instruction
            && in_array(
                $instruction->meeting_status,
                [OnlineMeeting::STATUS_GENERATED, OnlineMeeting::STATUS_MANUAL_REQUIRED],
                true
            );

        if (!$canHandle && !$hasCancelableInstruction && !$force) {
            Log::info('OnlineMeetingManager cancel skipped: no online context to cancel', [
                'appointment_id' => $appointment->id ?? null,
            ]);

            return OnlineMeetingResult::skipped(
                errorMessage: 'Appointment is not eligible for online meeting cancellation.',
                meta: ['appointment_id' => $appointment->id]
            );
        }

        $providerKey = $options['provider'] ?? ($instruction?->meeting_provider ?: $this->defaultProvider());
        $provider = $this->providerFor(is_string($providerKey) ? $providerKey : null);

        $providerKeySafe = $this->safeProviderKey($provider, is_string($providerKey) ? $providerKey : null);

        Log::info('OnlineMeetingManager cancel started', [
            'appointment_id' => $appointment->id ?? null,
            'provider' => $providerKeySafe,
        ]);

        try {
            $result = $provider->cancelForAppointment($appointment);

            if ($instruction && in_array($result->status, [OnlineMeeting::STATUS_CANCELLED, OnlineMeeting::STATUS_SKIPPED], true)) {
                $this->markInstructionCancelled($instruction, $appointment);
            }

            Log::info('OnlineMeetingManager cancel finished', [
                'appointment_id' => $appointment->id ?? null,
                'provider' => $providerKeySafe,
                'result_status' => $result->status,
                'result_success' => $result->success,
            ]);

            return $result;
        } catch (Throwable $e) {
            Log::error('OnlineMeetingManager cancel failed with exception', [
                'appointment_id' => $appointment->id ?? null,
                'provider' => $providerKeySafe,
                'error' => $e->getMessage(),
            ]);

            return OnlineMeetingResult::failed(
                provider: $providerKeySafe,
                errorMessage: 'Failed to cancel online meeting.',
                meta: [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]
            );
        }
    }

    public function shouldHandle(Appointment $appointment): bool
    {
        if (!filled($appointment->id)) {
            return false;
        }

        return strtolower(trim((string) $appointment->appointment_mode)) === 'online';
    }

    /**
     * @param array<string, mixed> $options
     */
    public function shouldProvisionNow(Appointment $appointment, array $options = []): bool
    {
        if (!$this->shouldHandle($appointment)) {
            return false;
        }

        if ($this->flag($options, 'force')) {
            return true;
        }

        if (!$this->flag($options, 'ignore_auto_generate_disabled') && !$this->autoGenerateEnabled()) {
            return false;
        }

        $timing = $this->timingFromOptions($options);
        $status = strtolower(trim((string) $appointment->status));

        if ($timing === OnlineMeeting::GENERATION_ON_CREATED) {
            return !in_array($status, ['expired', 'canceled', 'cancelled', 'no_show'], true);
        }

        return in_array($status, ['scheduled', 'rescheduled'], true);
    }

    /**
     * @param array<int|string, mixed> $changed
     */
    public function shouldUpdate(Appointment $appointment, array $changed = []): bool
    {
        $fields = $this->extractChangedFields($changed);

        foreach (['starts_at', 'ends_at', 'calendar_id', 'doctor_id', 'appointment_mode'] as $field) {
            if (in_array($field, $fields, true)) {
                return true;
            }
        }

        if (in_array('status', $fields, true)) {
            $status = strtolower(trim((string) $appointment->status));
            if (in_array($status, ['scheduled', 'rescheduled'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $changed
     */
    public function shouldCancel(Appointment $appointment, array $changed = []): bool
    {
        $fields = $this->extractChangedFields($changed);

        if (in_array('appointment_mode', $fields, true) && strtolower(trim((string) $appointment->appointment_mode)) !== 'online') {
            return true;
        }

        if (in_array('status', $fields, true)) {
            $status = strtolower(trim((string) $appointment->status));
            return in_array($status, ['canceled', 'cancelled', 'expired'], true);
        }

        return false;
    }

    public function defaultProvider(): string
    {
        $provider = (string) TenantSetting::get(
            'online_meetings.default_provider',
            OnlineMeeting::PROVIDER_GOOGLE_MEET
        );
        $provider = strtolower(trim($provider));

        if (!OnlineMeeting::isValidProvider($provider)) {
            return OnlineMeeting::PROVIDER_GOOGLE_MEET;
        }

        return $provider;
    }

    public function providerFor(?string $provider = null): OnlineMeetingProviderInterface
    {
        $resolved = strtolower(trim((string) ($provider ?? $this->defaultProvider())));

        if (!OnlineMeeting::isValidProvider($resolved)) {
            Log::warning('OnlineMeetingManager invalid provider resolved. Falling back to manual.', [
                'provider' => $provider,
            ]);
            return $this->manualProvider();
        }

        return match ($resolved) {
            OnlineMeeting::PROVIDER_MANUAL => $this->manualProvider(),
            OnlineMeeting::PROVIDER_GOOGLE_MEET => $this->googleProvider(),
            default => $this->manualProvider(),
        };
    }

    protected function manualProvider(): ManualMeetingProvider
    {
        return $this->manualMeetingProvider ?? app(ManualMeetingProvider::class);
    }

    protected function googleProvider(): GoogleMeetProvider
    {
        return $this->googleMeetProvider ?? app(GoogleMeetProvider::class);
    }

    protected function autoGenerateEnabled(): bool
    {
        $raw = TenantSetting::get('online_meetings.auto_generate_enabled', 'false');
        if (is_bool($raw)) {
            return $raw;
        }

        $normalized = strtolower(trim((string) $raw));

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }

    protected function generationTiming(): string
    {
        return OnlineMeeting::normalizeGenerationTiming((string) TenantSetting::get(
            'online_meetings.generation_timing',
            OnlineMeeting::GENERATION_ON_CONFIRMED
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function timingFromOptions(array $options, bool $useSettings = true): ?string
    {
        if (isset($options['timing']) && is_string($options['timing'])) {
            return OnlineMeeting::normalizeGenerationTiming($options['timing']);
        }

        return $useSettings ? $this->generationTiming() : null;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function resolveProviderKeyForProvision(array $options): string
    {
        if (isset($options['provider']) && is_string($options['provider']) && trim($options['provider']) !== '') {
            $raw = strtolower(trim($options['provider']));
            if (OnlineMeeting::isValidProvider($raw)) {
                return $raw;
            }

            Log::warning('OnlineMeetingManager invalid provider option. Falling back to manual.', [
                'provider' => $options['provider'],
            ]);

            return OnlineMeeting::PROVIDER_MANUAL;
        }

        $default = $this->defaultProvider();
        if (!OnlineMeeting::isValidProvider($default)) {
            return OnlineMeeting::PROVIDER_MANUAL;
        }

        return $default;
    }

    protected function instructionFrom(Appointment $appointment): ?OnlineAppointmentInstruction
    {
        if ($appointment->relationLoaded('onlineInstructions')) {
            return $appointment->getRelation('onlineInstructions');
        }

        if (!$appointment->exists) {
            return null;
        }

        return $appointment->onlineInstructions;
    }

    protected function hasGeneratedMeeting(?OnlineAppointmentInstruction $instruction): bool
    {
        return $instruction
            && $instruction->meeting_status === OnlineMeeting::STATUS_GENERATED
            && filled($instruction->meeting_link);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function flag(array $options, string $key): bool
    {
        if (!array_key_exists($key, $options)) {
            return false;
        }

        $value = $options[$key];
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    protected function loadProvisionContext(Appointment $appointment): void
    {
        if (!$appointment->exists) {
            return;
        }

        $appointment->loadMissing([
            'onlineInstructions',
            'calendar.doctor.googleCalendarToken',
            'calendar.doctor.user',
            'patient',
            'type',
            'specialty',
        ]);
    }

    /**
     * @param array<int|string, mixed> $changed
     * @return array<int, string>
     */
    protected function extractChangedFields(array $changed): array
    {
        if ($changed === []) {
            return [];
        }

        $isList = array_keys($changed) === range(0, count($changed) - 1);
        if ($isList) {
            return array_values(array_unique(array_map(static fn ($field): string => (string) $field, $changed)));
        }

        return array_values(array_unique(array_map(static fn ($field): string => (string) $field, array_keys($changed))));
    }

    protected function markInstructionCancelled(OnlineAppointmentInstruction $instruction, Appointment $appointment): void
    {
        $instruction->meeting_status = OnlineMeeting::STATUS_CANCELLED;
        $instruction->meeting_generation_error = null;

        if ($appointment->exists && $instruction->exists) {
            try {
                $instruction->save();
            } catch (Throwable $e) {
                Log::warning('OnlineMeetingManager could not persist cancelled status on instruction', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function safeProviderKey(OnlineMeetingProviderInterface $provider, ?string $fallback = null): string
    {
        try {
            return (string) $provider->key();
        } catch (Throwable) {
            if ($fallback !== null && trim($fallback) !== '') {
                return $fallback;
            }

            return OnlineMeeting::PROVIDER_MANUAL;
        }
    }
}
