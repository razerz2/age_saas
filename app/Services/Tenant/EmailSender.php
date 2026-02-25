<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\MailTenantService;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailSender
{
    public function __construct(
        private readonly NotificationDeliveryLogger $deliveryLogger
    ) {
    }

    public function send(string $tenantId, string $to, string $subject, string $message, array $meta = []): bool
    {
        return $this->sendInternal($tenantId, $to, $subject, $message, $meta, []);
    }

    /**
     * @param array<int,array{path:string,filename?:string,mime?:string}> $attachments
     */
    public function sendCampaign(
        string $tenantId,
        string $to,
        string $subject,
        string $message,
        array $attachments = [],
        array $meta = []
    ): bool {
        return $this->sendInternal($tenantId, $to, $subject, $message, $meta, $attachments);
    }

    /**
     * @param array<int,array{path:string,filename?:string,mime?:string}> $attachments
     */
    private function sendInternal(
        string $tenantId,
        string $to,
        string $subject,
        string $message,
        array $meta = [],
        array $attachments = []
    ): bool
    {
        $payloadMeta = $this->sanitizeMeta($meta);
        $normalizedMessage = str_replace(["\r\n", "\r"], "\n", $message);
        $key = (string) ($payloadMeta['key'] ?? 'unknown');
        $provider = $this->resolveProvider();

        try {
            MailTenantService::send($to, $subject, $normalizedMessage, [], $attachments);

            $logPayload = array_merge($payloadMeta, [
                'tenant_id' => trim($tenantId) !== '' ? $tenantId : null,
                'channel' => 'email',
                'to_masked' => $this->maskEmail($to),
                'subject_sha256' => hash('sha256', $subject),
                'subject_length' => strlen($subject),
                'message_sha256' => hash('sha256', $normalizedMessage),
                'message_length' => strlen($normalizedMessage),
                'sent' => true,
            ]);

            if ($this->shouldLogBody()) {
                $logPayload['subject'] = $subject;
                $logPayload['message'] = $normalizedMessage;
            }

            Log::info('email_real_send', $logPayload);

            $this->deliveryLogger->logSuccess(
                $tenantId,
                'email',
                $key,
                $provider,
                $to,
                $subject,
                $normalizedMessage,
                $payloadMeta
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('email_real_send_failed', array_merge($payloadMeta, [
                'tenant_id' => trim($tenantId) !== '' ? $tenantId : null,
                'channel' => 'email',
                'to_masked' => $this->maskEmail($to),
                'error' => $e->getMessage(),
            ]));

            $this->deliveryLogger->logError(
                $tenantId,
                'email',
                $key,
                $provider,
                $to,
                $subject,
                $normalizedMessage,
                $e,
                $payloadMeta
            );

            return false;
        }
    }

    private function maskEmail(string $email): string
    {
        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);
        if ($local === '') {
            return '***@' . $domain;
        }

        if (strlen($local) === 1) {
            return '*@' . $domain;
        }

        return substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 2, 1)) . substr($local, -1) . '@' . $domain;
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
            'is_override',
                'run_id',
                'campaign_id',
                'campaign_run_id',
                'campaign_recipient_id',
                'destination',
                'channel',
                'media_source',
                'media_kind',
                'asset_id',
                'provider_message_id',
                'http_status',
                'unknown_placeholders',
            ];

        $sanitized = [];
        foreach ($allowedKeys as $allowedKey) {
            if (!array_key_exists($allowedKey, $meta)) {
                continue;
            }

            $value = $meta[$allowedKey];
            if ($value === null || is_scalar($value) || is_array($value)) {
                $sanitized[$allowedKey] = $value;
            }
        }

        return $sanitized;
    }

    private function resolveProvider(): string
    {
        $provider = TenantSetting::emailProvider();
        $driver = strtolower((string) ($provider['driver'] ?? 'global'));

        if ($driver === 'tenancy') {
            return 'mail:tenant_smtp';
        }

        $defaultMailer = trim((string) config('mail.default', 'smtp'));
        if ($defaultMailer === '') {
            $defaultMailer = 'smtp';
        }

        return 'mail:' . $defaultMailer;
    }

    private function shouldLogBody(): bool
    {
        if ((bool) config('app.debug', false)) {
            return true;
        }

        return filter_var((string) env('NOTIFICATION_LOG_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
