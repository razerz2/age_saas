<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsappTenantService;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
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
                'provider' => $provider,
                'to_masked' => $this->maskPhone($to),
                'message_sha256' => hash('sha256', $normalizedMessage),
                'message_length' => strlen($normalizedMessage),
                'contains_especialidade' => str_contains(strtolower($normalizedMessage), 'especialidade'),
                'template_resolution_scope' => $payloadMeta['template_resolution_scope'] ?? null,
                'template_source' => $payloadMeta['template_source'] ?? null,
                'used_platform_fallback' => $payloadMeta['used_platform_fallback'] ?? null,
                'template_fallback_reason' => $payloadMeta['template_fallback_reason'] ?? null,
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
                'provider' => $provider,
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
            'template_resolution_scope',
            'used_platform_fallback',
            'template_fallback_reason',
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

        $globalProvider = $this->resolveTenantGlobalProvider($provider);
        if ($globalProvider === null) {
            return 'whatsapp:invalid_global_provider';
        }

        return 'whatsapp:' . $globalProvider;
    }

    private function applyTenantWhatsAppConfig(): void
    {
        $providerSettings = TenantSetting::whatsappProvider();
        $resolver = new ProviderConfigResolver();
        $driver = strtolower(trim((string) ($providerSettings['driver'] ?? 'global')));
        $globalProvider = $this->resolveTenantGlobalProvider($providerSettings);
        $effectiveGlobalProvider = $globalProvider ?? '__invalid_tenant_global_provider__';
        $globalMetaApiUrl = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_BASE_URL', 'WHATSAPP_BUSINESS_API_URL', 'WHATSAPP_API_URL'],
            (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0')
        );
        $globalMetaToken = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
            (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
        );
        $globalMetaPhoneId = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
            (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
        );
        $globalMetaWabaId = $this->resolveGlobalWhatsAppMetaValue(
            ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
            (string) config('services.whatsapp.business.waba_id', '')
        );

        config([
            'services.whatsapp.force_runtime_provider' => true,
            'services.whatsapp.runtime_provider' => $driver === 'global'
                ? strtolower(trim($effectiveGlobalProvider))
                : (strtolower(trim((string) ($providerSettings['provider'] ?? 'whatsapp_business'))) ?: 'whatsapp_business'),
            'services.whatsapp.provider' => $driver === 'global'
                ? $effectiveGlobalProvider
                : ($providerSettings['provider'] ?? 'whatsapp_business'),
            'services.whatsapp.business.api_url' => $driver === 'global'
                ? $globalMetaApiUrl
                : config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
            'services.whatsapp.business.token' => $driver === 'global'
                ? $globalMetaToken
                : ($providerSettings['meta_access_token'] ?? ''),
            'services.whatsapp.business.phone_id' => $driver === 'global'
                ? $globalMetaPhoneId
                : ($providerSettings['meta_phone_number_id'] ?? ''),
            'services.whatsapp.business.waba_id' => $driver === 'global'
                ? $globalMetaWabaId
                : ($providerSettings['meta_waba_id'] ?? ''),
            'services.whatsapp.zapi.api_url' => $driver === 'global'
                ? config('services.whatsapp.zapi.api_url', 'https://api.z-api.io')
                : ($providerSettings['zapi_api_url'] ?? 'https://api.z-api.io'),
            'services.whatsapp.zapi.token' => $driver === 'global'
                ? config('services.whatsapp.zapi.token', '')
                : ($providerSettings['zapi_token'] ?? ''),
            'services.whatsapp.zapi.client_token' => $driver === 'global'
                ? config('services.whatsapp.zapi.client_token', '')
                : ($providerSettings['zapi_client_token'] ?? ''),
            'services.whatsapp.zapi.instance_id' => $driver === 'global'
                ? config('services.whatsapp.zapi.instance_id', '')
                : ($providerSettings['zapi_instance_id'] ?? ''),
        ]);

        $resolver->applyUnofficialRuntimeConfigs($providerSettings);
    }

    /**
     * @param array<string, mixed> $providerSettings
     */
    private function resolveTenantGlobalProvider(array $providerSettings): ?string
    {
        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);
        return $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(
            (string) ($providerSettings['global_provider'] ?? '')
        );
    }

    private function resolveGlobalWhatsAppMetaValue(array $keys, string $fallback = ''): string
    {
        foreach ($keys as $key) {
            $value = function_exists('sysconfig')
                ? (string) sysconfig((string) $key, '')
                : '';

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return trim($fallback);
    }

    private function shouldLogBody(): bool
    {
        if ((bool) config('app.debug', false)) {
            return true;
        }

        return filter_var((string) env('NOTIFICATION_LOG_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
