<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\WhatsappTenantService;
use App\Services\WhatsApp\WahaClient;
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

    public function sendMediaFromUrl(
        string $tenantId,
        string $to,
        string $mediaUrl,
        ?string $caption = null,
        array $meta = []
    ): bool {
        $payloadMeta = $this->sanitizeMeta($meta);
        $key = (string) ($payloadMeta['key'] ?? 'unknown');
        $provider = $this->resolveProvider();
        $auditMessage = trim(($caption ? $caption . "\n" : '') . $mediaUrl);

        try {
            $this->applyTenantWhatsAppConfig();

            if (!str_contains(strtolower($provider), 'waha')) {
                throw new RuntimeException('Envio de mídia disponível apenas para o provedor WAHA neste MVP.');
            }

            $chatId = WahaClient::formatChatIdFromPhone($to);
            if ($chatId === '') {
                throw new RuntimeException('Telefone inválido para envio de mídia via WhatsApp.');
            }

            $client = WahaClient::fromConfig();
            if (!$client->isConfigured()) {
                throw new RuntimeException('WAHA não configurado corretamente para envio de mídia.');
            }

            $sessionResult = $client->getSessionStatus();
            $sessionBody = is_array($sessionResult['body'] ?? null) ? $sessionResult['body'] : [];
            $sessionState = strtoupper((string) ($sessionBody['status'] ?? $sessionBody['state'] ?? ''));
            $workingStates = ['WORKING', 'CONNECTED', 'READY', 'ONLINE'];

            if (empty($sessionResult['ok']) || !in_array($sessionState, $workingStates, true)) {
                throw new RuntimeException('Sessão WAHA indisponível para envio de mídia.');
            }

            $sendResult = $client->sendFileFromUrl($chatId, $mediaUrl, $caption);
            $sendBody = is_array($sendResult['body'] ?? null) ? $sendResult['body'] : [];

            if (empty($sendResult['ok']) || isset($sendBody['error'])) {
                $message = isset($sendBody['error'])
                    ? (string) $sendBody['error']
                    : 'WAHA retornou falha ao enviar mídia.';

                throw new RuntimeException($message);
            }

            $this->deliveryLogger->logSuccess(
                $tenantId,
                'whatsapp',
                $key,
                $provider,
                $to,
                null,
                $auditMessage !== '' ? $auditMessage : $mediaUrl,
                $payloadMeta
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('whatsapp_media_send_failed', array_merge($payloadMeta, [
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
                $auditMessage !== '' ? $auditMessage : $mediaUrl,
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

    private function applyTenantWhatsAppConfig(): void
    {
        $providerSettings = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($providerSettings['driver'] ?? 'global')));
        $globalProvider = function_exists('sysconfig')
            ? sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : config('services.whatsapp.provider', 'whatsapp_business');

        config([
            'services.whatsapp.provider' => $driver === 'global'
                ? $globalProvider
                : ($providerSettings['provider'] ?? 'whatsapp_business'),
            'services.whatsapp.business.api_url' => config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v18.0'),
            'services.whatsapp.business.token' => $providerSettings['meta_access_token'] ?? '',
            'services.whatsapp.business.phone_id' => $providerSettings['meta_phone_number_id'] ?? '',
            'services.whatsapp.zapi.api_url' => $providerSettings['zapi_api_url'] ?? 'https://api.z-api.io',
            'services.whatsapp.zapi.token' => $providerSettings['zapi_token'] ?? '',
            'services.whatsapp.zapi.client_token' => $providerSettings['zapi_client_token'] ?? '',
            'services.whatsapp.zapi.instance_id' => $providerSettings['zapi_instance_id'] ?? '',
            'services.whatsapp.waha.base_url' => $providerSettings['waha_base_url'] ?? '',
            'services.whatsapp.waha.api_key' => $providerSettings['waha_api_key'] ?? '',
            'services.whatsapp.waha.session' => $providerSettings['waha_session'] ?? 'default',
        ]);
    }

    private function shouldLogBody(): bool
    {
        if ((bool) config('app.debug', false)) {
            return true;
        }

        return filter_var((string) env('NOTIFICATION_LOG_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
