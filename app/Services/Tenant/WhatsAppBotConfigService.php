<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;

class WhatsAppBotConfigService
{
    public const FEATURE_NAME = 'whatsapp_bot';
    public const MODE_SHARED_WITH_NOTIFICATIONS = 'shared_with_notifications';
    public const MODE_DEDICATED = 'dedicated';

    /**
     * @var array<int, string>
     */
    public const SUPPORTED_PROVIDERS = ['whatsapp_business', 'zapi', 'waha', 'evolution'];

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return TenantSetting::whatsappBotProvider();
    }

    /**
     * Resolve o provider efetivo que o bot deve usar.
     *
     * @return array<string, mixed>
     */
    public function resolveEffectiveProviderConfig(?array $settings = null): array
    {
        $botSettings = $settings ?? $this->getSettings();
        $mode = $this->normalizeProviderMode((string) ($botSettings['provider_mode'] ?? self::MODE_SHARED_WITH_NOTIFICATIONS));

        if ($mode === self::MODE_SHARED_WITH_NOTIFICATIONS) {
            return $this->resolveSharedWithNotificationsConfig();
        }

        return [
            'mode' => self::MODE_DEDICATED,
            'source' => 'bot',
            'provider' => $this->normalizeProvider((string) ($botSettings['provider'] ?? 'whatsapp_business')),
            'meta_access_token' => (string) ($botSettings['meta_access_token'] ?? ''),
            'meta_phone_number_id' => (string) ($botSettings['meta_phone_number_id'] ?? ''),
            'meta_waba_id' => (string) ($botSettings['meta_waba_id'] ?? ''),
            'zapi_api_url' => (string) ($botSettings['zapi_api_url'] ?? ''),
            'zapi_token' => (string) ($botSettings['zapi_token'] ?? ''),
            'zapi_client_token' => (string) ($botSettings['zapi_client_token'] ?? ''),
            'zapi_instance_id' => (string) ($botSettings['zapi_instance_id'] ?? ''),
            'waha_base_url' => (string) ($botSettings['waha_base_url'] ?? ''),
            'waha_api_key' => (string) ($botSettings['waha_api_key'] ?? ''),
            'waha_session' => (string) ($botSettings['waha_session'] ?? 'default'),
            'evolution_base_url' => (string) ($botSettings['evolution_base_url'] ?? ''),
            'evolution_api_key' => (string) ($botSettings['evolution_api_key'] ?? ''),
            'evolution_instance' => (string) ($botSettings['evolution_instance'] ?? 'default'),
        ];
    }

    public function normalizeProviderMode(string $mode): string
    {
        return in_array($mode, [self::MODE_SHARED_WITH_NOTIFICATIONS, self::MODE_DEDICATED], true)
            ? $mode
            : self::MODE_SHARED_WITH_NOTIFICATIONS;
    }

    public function normalizeProvider(string $provider): string
    {
        $normalized = strtolower(trim($provider));

        return in_array($normalized, self::SUPPORTED_PROVIDERS, true)
            ? $normalized
            : 'whatsapp_business';
    }

    public function providerLabel(string $provider): string
    {
        return match ($this->normalizeProvider($provider)) {
            'zapi' => 'Z-API',
            'waha' => 'WAHA',
            'evolution' => 'Evolution API',
            default => 'WhatsApp Business (Meta)',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSharedWithNotificationsConfig(): array
    {
        $notificationConfig = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($notificationConfig['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            return [
                'mode' => self::MODE_SHARED_WITH_NOTIFICATIONS,
                'source' => 'notifications',
                'provider' => $this->normalizeProvider((string) ($notificationConfig['provider'] ?? 'whatsapp_business')),
                'meta_access_token' => (string) ($notificationConfig['meta_access_token'] ?? ''),
                'meta_phone_number_id' => (string) ($notificationConfig['meta_phone_number_id'] ?? ''),
                'meta_waba_id' => (string) ($notificationConfig['meta_waba_id'] ?? ''),
                'zapi_api_url' => (string) ($notificationConfig['zapi_api_url'] ?? ''),
                'zapi_token' => (string) ($notificationConfig['zapi_token'] ?? ''),
                'zapi_client_token' => (string) ($notificationConfig['zapi_client_token'] ?? ''),
                'zapi_instance_id' => (string) ($notificationConfig['zapi_instance_id'] ?? ''),
                'waha_base_url' => (string) ($notificationConfig['waha_base_url'] ?? ''),
                'waha_api_key' => (string) ($notificationConfig['waha_api_key'] ?? ''),
                'waha_session' => (string) ($notificationConfig['waha_session'] ?? 'default'),
            ];
        }

        $tenantGlobalProviderCatalog = app(TenantGlobalProviderCatalogService::class);
        $globalProvider = $tenantGlobalProviderCatalog->resolveTenantGlobalProvider(
            (string) ($notificationConfig['global_provider'] ?? '')
        );

        $resolvedWahaConfig = app(ProviderConfigResolver::class)->resolveWahaConfig($notificationConfig);
        $resolvedEvolutionConfig = app(ProviderConfigResolver::class)->resolveEvolutionConfig($notificationConfig);

        return [
            'mode' => self::MODE_SHARED_WITH_NOTIFICATIONS,
            'source' => 'notifications',
            'provider' => $globalProvider !== null
                ? $this->normalizeProvider($globalProvider)
                : 'whatsapp_business',
            'meta_access_token' => $this->resolveGlobalValue(
                ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
                (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
            ),
            'meta_phone_number_id' => $this->resolveGlobalValue(
                ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
                (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
            ),
            'meta_waba_id' => $this->resolveGlobalValue(
                ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
                (string) config('services.whatsapp.business.waba_id', '')
            ),
            'zapi_api_url' => (string) config('services.whatsapp.zapi.api_url', 'https://api.z-api.io'),
            'zapi_token' => (string) config('services.whatsapp.zapi.token', ''),
            'zapi_client_token' => (string) config('services.whatsapp.zapi.client_token', ''),
            'zapi_instance_id' => (string) config('services.whatsapp.zapi.instance_id', ''),
            'waha_base_url' => (string) ($resolvedWahaConfig['base_url'] ?? ''),
            'waha_api_key' => (string) ($resolvedWahaConfig['api_key'] ?? ''),
            'waha_session' => (string) ($resolvedWahaConfig['session'] ?? 'default'),
            'evolution_base_url' => (string) ($resolvedEvolutionConfig['base_url'] ?? ''),
            'evolution_api_key' => (string) ($resolvedEvolutionConfig['api_key'] ?? ''),
            'evolution_instance' => (string) ($resolvedEvolutionConfig['instance'] ?? 'default'),
        ];
    }

    private function resolveGlobalValue(array $keys, string $fallback = ''): string
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
}
