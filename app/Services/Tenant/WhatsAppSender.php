<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\WhatsappTenantService;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppSender
{
    public function __construct(
        private readonly NotificationDeliveryLogger $deliveryLogger
    ) {
    }

    public function send(string $tenantId, string $to, string $message, array $meta = []): bool
    {
        $normalizedMessage = str_replace(["\r\n", "\r"], "\n", $message);
        $payloadMeta = $this->sanitizeMeta($meta);
        $key = (string) ($payloadMeta['key'] ?? 'unknown');
        $provider = $this->resolveProvider();

        try {
            $sent = WhatsappTenantService::send($to, $normalizedMessage);

            $logPayload = array_merge($payloadMeta, [
                'tenant_id' => trim($tenantId) !== '' ? $tenantId : null,
                'channel' => 'whatsapp',
                'to_masked' => $this->maskPhone($to),
                'message_sha256' => hash('sha256', $normalizedMessage),
                'message_length' => strlen($normalizedMessage),
                'contains_especialidade' => str_contains(strtolower($normalizedMessage), 'especialidade'),
                'sent' => $sent,
            ]);

            if ($this->shouldLogBody()) {
                $logPayload['message'] = $normalizedMessage;
            }

            Log::info('whatsapp_real_send', $logPayload);

            if ($sent) {
                $this->deliveryLogger->logSuccess(
                    $tenantId,
                    'whatsapp',
                    $key,
                    $provider,
                    $to,
                    null,
                    $normalizedMessage,
                    $payloadMeta
                );
            } else {
                $this->deliveryLogger->logError(
                    $tenantId,
                    'whatsapp',
                    $key,
                    $provider,
                    $to,
                    null,
                    $normalizedMessage,
                    new RuntimeException('WhatsApp provider returned unsuccessful response.'),
                    $payloadMeta
                );
            }

            return $sent;
        } catch (Throwable $e) {
            Log::warning('whatsapp_real_send_failed', array_merge($payloadMeta, [
                'tenant_id' => trim($tenantId) !== '' ? $tenantId : null,
                'channel' => 'whatsapp',
                'to_masked' => $this->maskPhone($to),
                'error' => $e->getMessage(),
            ]));

            $this->deliveryLogger->logError(
                $tenantId,
                'whatsapp',
                $key,
                $provider,
                $to,
                null,
                $normalizedMessage,
                $e,
                $payloadMeta
            );

            return false;
        }
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '***';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
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
        $provider = TenantSetting::whatsappProvider();
        $driver = strtolower((string) ($provider['driver'] ?? 'global'));

        if ($driver === 'tenancy') {
            $providerName = trim((string) ($provider['provider'] ?? ''));
            if ($providerName !== '') {
                return 'whatsapp:' . $providerName;
            }

            if (!empty($provider['api_url'])) {
                return 'whatsapp:tenant_legacy_api';
            }

            return 'whatsapp:tenant';
        }

        $globalProvider = null;
        if (function_exists('sysconfig')) {
            $globalProvider = sysconfig('WHATSAPP_PROVIDER');
        }

        $globalProvider = trim((string) ($globalProvider ?: config('services.whatsapp.provider', 'whatsapp_business')));
        if ($globalProvider === '') {
            $globalProvider = 'whatsapp_business';
        }

        return 'whatsapp:' . $globalProvider;
    }

    private function shouldLogBody(): bool
    {
        if ((bool) config('app.debug', false)) {
            return true;
        }

        return filter_var((string) env('NOTIFICATION_LOG_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
