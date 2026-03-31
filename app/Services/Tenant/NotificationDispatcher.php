<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Models\Tenant\FormResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class NotificationDispatcher
{
    public const CHANNELS = ['email', 'whatsapp'];
    private const AUDIENCES = ['patient', 'doctor'];
    private const DOCTOR_TEMPLATE_KEY_MAP = [
        'appointment.pending_confirmation' => 'appointment.created.doctor',
        'appointment.confirmed' => 'appointment.confirmed.doctor',
        'appointment.canceled' => 'appointment.canceled.doctor',
        'appointment.rescheduled' => 'appointment.rescheduled.doctor',
        'waitlist.offered' => 'waitlist.offered.doctor',
        'waitlist.accepted' => 'waitlist.accepted.doctor',
        'form.response_submitted' => 'form.response_submitted.doctor',
        'online_appointment.updated' => 'online_appointment.updated.doctor',
        'online_appointment.instructions_sent' => 'online_appointment.instructions_sent.doctor',
        'online_appointment.form_response_submitted' => 'online_appointment.form_response_submitted.doctor',
    ];

    public function __construct(
        private readonly WhatsAppUnofficialTemplateResolutionService $whatsAppUnofficialTemplateResolutionService,
        private readonly NotificationTemplateService $templateService,
        private readonly NotificationContextBuilder $contextBuilder,
        private readonly TemplateRenderer $renderer,
        private readonly WhatsAppSender $whatsAppSender,
        private readonly EmailSender $emailSender
    ) {
    }

    public function dispatchAppointment(Appointment $appointment, string $key, array $meta = []): void
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId === null) {
            Log::warning('Nao foi possivel despachar notificacao de agendamento: tenant ausente.', [
                'key' => $key,
                'appointment_id' => (string) $appointment->id,
            ]);
            return;
        }

        try {
            $context = $this->contextBuilder->buildForAppointment($appointment);
        } catch (Throwable $e) {
            Log::warning('Falha ao montar contexto de notificacao de agendamento.', [
                'tenant_id' => $tenantId,
                'key' => $key,
                'appointment_id' => (string) $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $this->dispatchWithContext($tenantId, $key, $context, array_merge($meta, [
            'appointment_id' => (string) $appointment->id,
        ]));
    }

    public function dispatchWaitlist(AppointmentWaitlistEntry $entry, string $key, array $meta = []): void
    {
        $tenantId = $this->resolveTenantId((string) $entry->tenant_id);
        if ($tenantId === null) {
            Log::warning('Nao foi possivel despachar notificacao de waitlist: tenant ausente.', [
                'key' => $key,
                'waitlist_entry_id' => (string) $entry->id,
            ]);
            return;
        }

        try {
            $context = $this->contextBuilder->buildForWaitlistOffer($entry);
        } catch (Throwable $e) {
            Log::warning('Falha ao montar contexto de notificacao de waitlist.', [
                'tenant_id' => $tenantId,
                'key' => $key,
                'waitlist_entry_id' => (string) $entry->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $this->dispatchWithContext($tenantId, $key, $context, array_merge($meta, [
            'waitlist_entry_id' => (string) $entry->id,
        ]));
    }

    public function dispatchFormResponse(FormResponse $formResponse, string $key, array $meta = []): void
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId === null) {
            Log::warning('Nao foi possivel despachar notificacao de resposta de formulario: tenant ausente.', [
                'key' => $key,
                'form_response_id' => (string) $formResponse->id,
            ]);
            return;
        }

        try {
            $context = $this->contextBuilder->buildForFormResponse($formResponse);
        } catch (Throwable $e) {
            Log::warning('Falha ao montar contexto de notificacao de resposta de formulario.', [
                'tenant_id' => $tenantId,
                'key' => $key,
                'form_response_id' => (string) $formResponse->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $this->dispatchWithContext($tenantId, $key, $context, array_merge($meta, [
            'form_response_id' => (string) $formResponse->id,
        ]));
    }

    /**
     * @return array<string, array{
     *     channel:string,
     *     key:string,
     *     subject:?string,
     *     message:string,
     *     template_source:string,
     *     is_override:bool,
     *     unknown_placeholders:list<string>,
     *     template_resolution_scope:?string,
     *     used_platform_fallback:bool,
     *     template_fallback_reason:?string
     * }>
     */
    public function buildMessageForAppointment(Appointment $appointment, string $key, ?array $channels = null, array $meta = []): array
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId === null) {
            throw new RuntimeException('Tenant ausente para renderizar template de agendamento.');
        }

        $context = $this->contextBuilder->buildForAppointment($appointment);
        $baseMeta = $this->sanitizeMeta(array_merge($meta, [
            'appointment_id' => (string) $appointment->id,
        ]));

        $payloads = [];
        foreach ($this->normalizeChannels($channels) as $channel) {
            $payloads[$channel] = $this->buildChannelPayload($tenantId, $channel, $key, $context, $baseMeta);
        }

        return $payloads;
    }

    private function dispatchWithContext(string $tenantId, string $key, array $context, array $meta = []): void
    {
        $baseMeta = $this->sanitizeMeta($meta);
        $suppressedChannels = $this->normalizeSuppressedChannels($baseMeta['suppress_channels'] ?? null);
        $suppressedChannelsByAudience = [
            'patient' => $this->normalizeSuppressedChannels($baseMeta['suppress_patient_channels'] ?? null),
            'doctor' => $this->normalizeSuppressedChannels($baseMeta['suppress_doctor_channels'] ?? null),
        ];

        foreach (self::CHANNELS as $channel) {
            if (in_array($channel, $suppressedChannels, true)) {
                Log::info('notification_channel_suppressed', array_merge($baseMeta, [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'key' => $key,
                    'scope' => 'global',
                ]));
                continue;
            }

            foreach (self::AUDIENCES as $audience) {
                if (in_array($channel, $suppressedChannelsByAudience[$audience], true)) {
                    Log::info('notification_channel_suppressed', array_merge($baseMeta, [
                        'tenant_id' => $tenantId,
                        'channel' => $channel,
                        'audience' => $audience,
                        'key' => $key,
                        'scope' => 'audience',
                    ]));
                    continue;
                }

                if (!$this->isChannelEnabled($channel, $audience)) {
                    Log::info('notification_channel_disabled', array_merge($baseMeta, [
                        'tenant_id' => $tenantId,
                        'channel' => $channel,
                        'audience' => $audience,
                        'key' => $key,
                    ]));
                    continue;
                }

                $resolvedKey = $this->resolveAudienceTemplateKey($key, $audience);
                if ($resolvedKey === null) {
                    continue;
                }

                $recipientPath = $this->resolveRecipientPath($channel, $audience);
                if ($recipientPath === null) {
                    continue;
                }

                $to = $this->normalizeRecipient(data_get($context, $recipientPath));
                if ($to === null) {
                    Log::warning('notification_missing_recipient', array_merge($baseMeta, [
                        'tenant_id' => $tenantId,
                        'channel' => $channel,
                        'audience' => $audience,
                        'key' => $resolvedKey,
                    ]));
                    continue;
                }

                try {
                    $payload = $this->buildChannelPayload($tenantId, $channel, $resolvedKey, $context, $baseMeta);
                    $renderLogPayload = array_merge($baseMeta, [
                        'tenant_id' => $tenantId,
                        'channel' => $channel,
                        'audience' => $audience,
                        'requested_key' => $key,
                        'key' => $payload['key'],
                        'template_source' => $payload['template_source'],
                        'template_resolution_scope' => $payload['template_resolution_scope'],
                        'used_platform_fallback' => $payload['used_platform_fallback'],
                        'template_fallback_reason' => $payload['template_fallback_reason'],
                        'subject_sha256' => $payload['subject'] !== null ? hash('sha256', $payload['subject']) : null,
                        'subject_length' => $payload['subject'] !== null ? strlen($payload['subject']) : 0,
                        'message_sha256' => hash('sha256', $payload['message']),
                        'message_length' => strlen($payload['message']),
                    ]);

                    if ($this->shouldLogBody()) {
                        $renderLogPayload['subject'] = $payload['subject'];
                        $renderLogPayload['message'] = $payload['message'];
                    }

                    Log::info('Notificacao renderizada por template.', $renderLogPayload);

                    $this->deliverToRecipient($tenantId, $to, $audience, $payload, $baseMeta);
                } catch (Throwable $e) {
                    Log::warning('Falha ao renderizar notificacao por template.', array_merge($baseMeta, [
                        'tenant_id' => $tenantId,
                        'channel' => $channel,
                        'audience' => $audience,
                        'requested_key' => $key,
                        'key' => $resolvedKey,
                        'error' => $e->getMessage(),
                    ]));
                }
            }
        }
    }

    /**
     * @return array{
     *     channel:string,
     *     key:string,
     *     subject:?string,
     *     message:string,
     *     template_source:string,
     *     is_override:bool,
     *     unknown_placeholders:list<string>,
     *     template_resolution_scope:?string,
     *     used_platform_fallback:bool,
     *     template_fallback_reason:?string
     * }
     */
    private function buildChannelPayload(string $tenantId, string $channel, string $key, array $context, array $baseMeta): array
    {
        $template = null;
        $subjectTemplate = '';
        $contentTemplate = '';
        $templateSource = 'default';
        $isOverride = false;
        $resolutionScope = null;
        $usedPlatformFallback = false;
        $templateFallbackReason = null;

        if ($channel === 'whatsapp') {
            $resolutionScope = $this->resolveWhatsAppTemplateScope($baseMeta);
            $resolved = $this->whatsAppUnofficialTemplateResolutionService->resolve($tenantId, $key, $resolutionScope);

            if ($resolved !== null) {
                $contentTemplate = (string) ($resolved['content'] ?? '');
                $templateSource = trim((string) ($resolved['source'] ?? 'tenant_custom'));
                if ($templateSource === '') {
                    $templateSource = 'tenant_custom';
                }
                $usedPlatformFallback = (bool) ($resolved['used_platform_fallback'] ?? false);
                $isOverride = $templateSource === 'tenant_custom';
            } else {
                $templateFallbackReason = $resolutionScope === WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM
                    ? 'resolver_not_found_tenant_or_platform'
                    : 'resolver_not_found_tenant';
            }
        }

        if ($contentTemplate === '') {
            $template = $this->templateService->getEffectiveTemplate($tenantId, $channel, $key);
            $subjectTemplate = $channel === 'email' ? (string) ($template['subject'] ?? '') : '';
            $contentTemplate = (string) $template['content'];
            $isOverride = (bool) ($template['is_override'] ?? false);
            $templateSource = $isOverride ? 'override' : 'default';

            if ($channel === 'whatsapp' && $templateFallbackReason === null) {
                $templateFallbackReason = 'resolver_not_used';
            }
        }

        $placeholders = array_merge(
            $this->renderer->extractPlaceholders($contentTemplate),
            $this->renderer->extractPlaceholders($subjectTemplate)
        );
        $unknownPlaceholders = $this->findUnknownPlaceholders($placeholders, $context);
        if ($unknownPlaceholders !== []) {
            Log::warning('unknown_placeholders', array_merge($baseMeta, [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'key' => $key,
                'unknown_placeholders' => $unknownPlaceholders,
            ]));
        }

        $message = $this->normalizeMessageForChannel(
            $this->renderer->render($contentTemplate, $context),
            $channel
        );
        $subject = null;

        if ($channel === 'email' && ($template['subject'] ?? null) !== null) {
            $subject = $this->normalizeMessageForChannel(
                $this->renderer->render($subjectTemplate, $context),
                $channel
            );
        }

        return [
            'channel' => $channel,
            'key' => $key,
            'subject' => $subject,
            'message' => $message,
            'template_source' => $templateSource,
            'is_override' => $isOverride,
            'unknown_placeholders' => $unknownPlaceholders,
            'template_resolution_scope' => $resolutionScope,
            'used_platform_fallback' => $usedPlatformFallback,
            'template_fallback_reason' => $templateFallbackReason,
        ];
    }

    /**
     * @param  list<string>  $placeholders
     * @return list<string>
     */
    private function findUnknownPlaceholders(array $placeholders, array $context): array
    {
        if ($placeholders === []) {
            return [];
        }

        $missing = new \stdClass();
        $unknown = [];

        foreach ($placeholders as $placeholder) {
            $value = data_get($context, $placeholder, $missing);
            if ($value === $missing) {
                $unknown[] = $placeholder;
            }
        }

        return array_values(array_unique($unknown));
    }

    private function normalizeMessageForChannel(string $text, string $channel): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);

        if ($channel === 'whatsapp') {
            // WhatsApp: keep plain text and preserve explicit line breaks.
            return $normalized;
        }

        // Email: keep plain text with line breaks (no HTML conversion).
        return $normalized;
    }

    private function resolveTenantId(?string $fallbackTenantId = null): ?string
    {
        $tenant = tenant();
        $tenantId = $tenant?->id;
        if (is_string($tenantId) && trim($tenantId) !== '') {
            return $tenantId;
        }

        if ($fallbackTenantId !== null && trim($fallbackTenantId) !== '') {
            return $fallbackTenantId;
        }

        return null;
    }

    private function sanitizeMeta(array $meta): array
    {
        $allowedKeys = [
            'appointment_id',
            'waitlist_entry_id',
            'form_response_id',
            'origin',
            'event',
            'key',
            'template_source',
            'template_resolution_scope',
            'allow_platform_fallback',
            'used_platform_fallback',
            'template_fallback_reason',
            'run_id',
            'suppress_channels',
            'suppress_patient_channels',
            'suppress_doctor_channels',
        ];

        $sanitized = [];
        foreach ($allowedKeys as $allowedKey) {
            if (!array_key_exists($allowedKey, $meta)) {
                continue;
            }

            $value = $meta[$allowedKey];
            if (in_array($allowedKey, ['suppress_channels', 'suppress_patient_channels', 'suppress_doctor_channels'], true) && is_array($value)) {
                $sanitized[$allowedKey] = $this->normalizeSuppressedChannels($value);
                continue;
            }

            if ($value === null || is_scalar($value)) {
                $sanitized[$allowedKey] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeSuppressedChannels(mixed $value): array
    {
        $items = is_array($value) ? $value : [];
        $normalized = [];

        foreach ($items as $item) {
            $channel = strtolower(trim((string) $item));
            if (in_array($channel, self::CHANNELS, true) && !in_array($channel, $normalized, true)) {
                $normalized[] = $channel;
            }
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private function normalizeChannels(?array $channels): array
    {
        $channels = $channels ?? self::CHANNELS;
        if ($channels === []) {
            return self::CHANNELS;
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $channel = strtolower(trim((string) $channel));
            if (in_array($channel, self::CHANNELS, true)) {
                $normalized[] = $channel;
            }
        }

        if ($normalized === []) {
            return self::CHANNELS;
        }

        return array_values(array_unique($normalized));
    }

    private function resolveAudienceTemplateKey(string $key, string $audience): ?string
    {
        if ($audience === 'patient') {
            return str_ends_with($key, '.doctor') ? null : $key;
        }

        if ($audience === 'doctor') {
            if (str_ends_with($key, '.doctor')) {
                return $key;
            }

            return self::DOCTOR_TEMPLATE_KEY_MAP[$key] ?? null;
        }

        return null;
    }

    private function resolveRecipientPath(string $channel, string $audience): ?string
    {
        return match ($channel . ':' . $audience) {
            'whatsapp:patient' => 'patient.phone',
            'whatsapp:doctor' => 'doctor.phone',
            'email:patient' => 'patient.email',
            'email:doctor' => 'doctor.email',
            default => null,
        };
    }

    /**
     * @param  array{
     *     channel:string,
     *     key:string,
     *     subject:?string,
     *     message:string,
     *     template_source:string,
     *     is_override:bool,
     *     unknown_placeholders:list<string>,
     *     template_resolution_scope:?string,
     *     used_platform_fallback:bool,
     *     template_fallback_reason:?string
     * }  $payload
     */
    private function deliverToRecipient(string $tenantId, string $to, string $audience, array $payload, array $baseMeta): void
    {
        $channel = $payload['channel'];
        if ($channel === 'whatsapp') {
            $this->whatsAppSender->send($tenantId, $to, $payload['message'], array_merge($baseMeta, [
                'audience' => $audience,
                'key' => $payload['key'],
                'template_source' => $payload['template_source'],
                'is_override' => $payload['is_override'],
                'unknown_placeholders' => $payload['unknown_placeholders'],
                'template_resolution_scope' => $payload['template_resolution_scope'],
                'used_platform_fallback' => $payload['used_platform_fallback'],
                'template_fallback_reason' => $payload['template_fallback_reason'],
            ]));
            return;
        }

        if ($channel === 'email') {
            $this->emailSender->send(
                $tenantId,
                $to,
                (string) ($payload['subject'] ?? ''),
                $payload['message'],
                array_merge($baseMeta, [
                    'audience' => $audience,
                    'key' => $payload['key'],
                    'template_source' => $payload['template_source'],
                    'is_override' => $payload['is_override'],
                    'unknown_placeholders' => $payload['unknown_placeholders'],
                ])
            );
        }
    }

    private function isChannelEnabled(string $channel, string $audience = 'patient'): bool
    {
        $settingKey = match ($channel . ':' . $audience) {
            'email:patient' => 'notifications.send_email_to_patients',
            'whatsapp:patient' => 'notifications.send_whatsapp_to_patients',
            'email:doctor' => 'notifications.send_email_to_doctors',
            'whatsapp:doctor' => 'notifications.send_whatsapp_to_doctors',
            default => null,
        };

        if ($settingKey === null) {
            return false;
        }

        $value = TenantSetting::get($settingKey);
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    private function normalizeRecipient(mixed $recipient): ?string
    {
        if ($recipient === null) {
            return null;
        }

        $recipient = trim((string) $recipient);
        return $recipient === '' ? null : $recipient;
    }

    private function shouldLogBody(): bool
    {
        if ((bool) config('app.debug', false)) {
            return true;
        }

        return filter_var((string) env('NOTIFICATION_LOG_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    private function resolveWhatsAppTemplateScope(array $meta): string
    {
        $scope = strtolower(trim((string) ($meta['template_resolution_scope'] ?? '')));
        if ($scope === WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM) {
            return WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM;
        }

        $allowFallback = $meta['allow_platform_fallback'] ?? false;
        if (filter_var($allowFallback, FILTER_VALIDATE_BOOLEAN)) {
            return WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_THEN_PLATFORM;
        }

        return WhatsAppUnofficialTemplateResolutionService::SCOPE_TENANT_ONLY;
    }
}
