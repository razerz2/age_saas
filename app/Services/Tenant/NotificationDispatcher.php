<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentWaitlistEntry;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class NotificationDispatcher
{
    public const CHANNELS = ['email', 'whatsapp'];

    public function __construct(
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

    /**
     * @return array<string, array{
     *     channel:string,
     *     key:string,
     *     subject:?string,
     *     message:string,
     *     template_source:string,
     *     is_override:bool,
     *     unknown_placeholders:list<string>
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

        foreach (self::CHANNELS as $channel) {
            try {
                $payload = $this->buildChannelPayload($tenantId, $channel, $key, $context, $baseMeta);
                $renderLogPayload = array_merge($baseMeta, [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'key' => $key,
                    'template_source' => $payload['template_source'],
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

                $this->deliverChannel($tenantId, $payload, $context, $baseMeta);
            } catch (Throwable $e) {
                Log::warning('Falha ao renderizar notificacao por template.', array_merge($baseMeta, [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]));
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
     *     unknown_placeholders:list<string>
     * }
     */
    private function buildChannelPayload(string $tenantId, string $channel, string $key, array $context, array $baseMeta): array
    {
        $template = $this->templateService->getEffectiveTemplate($tenantId, $channel, $key);
        $subjectTemplate = $channel === 'email' ? (string) ($template['subject'] ?? '') : '';
        $contentTemplate = (string) $template['content'];

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

        $isOverride = (bool) ($template['is_override'] ?? false);

        return [
            'channel' => $channel,
            'key' => $key,
            'subject' => $subject,
            'message' => $message,
            'template_source' => $isOverride ? 'override' : 'default',
            'is_override' => $isOverride,
            'unknown_placeholders' => $unknownPlaceholders,
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
            'origin',
            'event',
            'key',
            'template_source',
            'run_id',
        ];

        $sanitized = [];
        foreach ($allowedKeys as $allowedKey) {
            if (!array_key_exists($allowedKey, $meta)) {
                continue;
            }

            $value = $meta[$allowedKey];
            if ($value === null || is_scalar($value)) {
                $sanitized[$allowedKey] = $value;
            }
        }

        return $sanitized;
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

    /**
     * @param  array{
     *     channel:string,
     *     key:string,
     *     subject:?string,
     *     message:string,
     *     template_source:string,
     *     is_override:bool,
     *     unknown_placeholders:list<string>
     * }  $payload
     */
    private function deliverChannel(string $tenantId, array $payload, array $context, array $baseMeta): void
    {
        $channel = $payload['channel'];
        if (!$this->isChannelEnabled($channel)) {
            Log::info('notification_channel_disabled', array_merge($baseMeta, [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'key' => $payload['key'],
            ]));
            return;
        }

        if ($channel === 'whatsapp') {
            $to = $this->normalizeRecipient(data_get($context, 'patient.phone'));
            if ($to === null) {
                Log::warning('notification_missing_recipient', array_merge($baseMeta, [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'key' => $payload['key'],
                ]));
                return;
            }

            $this->whatsAppSender->send($tenantId, $to, $payload['message'], array_merge($baseMeta, [
                'key' => $payload['key'],
                'template_source' => $payload['template_source'],
                'is_override' => $payload['is_override'],
                'unknown_placeholders' => $payload['unknown_placeholders'],
            ]));

            return;
        }

        if ($channel === 'email') {
            $to = $this->normalizeRecipient(data_get($context, 'patient.email'));
            if ($to === null) {
                Log::warning('notification_missing_recipient', array_merge($baseMeta, [
                    'tenant_id' => $tenantId,
                    'channel' => $channel,
                    'key' => $payload['key'],
                ]));
                return;
            }

            $this->emailSender->send(
                $tenantId,
                $to,
                (string) ($payload['subject'] ?? ''),
                $payload['message'],
                array_merge($baseMeta, [
                    'key' => $payload['key'],
                    'template_source' => $payload['template_source'],
                    'is_override' => $payload['is_override'],
                    'unknown_placeholders' => $payload['unknown_placeholders'],
                ])
            );
        }
    }

    private function isChannelEnabled(string $channel): bool
    {
        $settingKey = match ($channel) {
            'email' => 'notifications.send_email_to_patients',
            'whatsapp' => 'notifications.send_whatsapp_to_patients',
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
}
