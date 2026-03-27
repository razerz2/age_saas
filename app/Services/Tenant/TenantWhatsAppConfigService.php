<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\Providers\ProviderConfigResolver;

class TenantWhatsAppConfigService
{
    public function __construct(
        private readonly ProviderConfigResolver $providerConfigResolver
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = TenantSetting::whatsappProvider();
        $config['meta_waba_id'] = (string) TenantSetting::get('whatsapp.meta.waba_id', '');

        return $config;
    }

    public function isOwnOfficialProviderEnabled(): bool
    {
        $config = $this->getConfig();

        $driver = strtolower(trim((string) ($config['driver'] ?? 'global')));
        $provider = strtolower(trim((string) ($config['provider'] ?? '')));

        return $driver === 'tenancy' && in_array($provider, ['whatsapp_business', 'business'], true);
    }

    public function applyRuntimeConfig(): void
    {
        $config = $this->getConfig();
        $driver = strtolower(trim((string) ($config['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            $provider = strtolower(trim((string) ($config['provider'] ?? 'whatsapp_business')));
            if ($provider === '') {
                $provider = 'whatsapp_business';
            }

            config([
                'services.whatsapp.force_runtime_provider' => true,
                'services.whatsapp.runtime_provider' => $provider,
                'services.whatsapp.provider' => $provider,
                'services.whatsapp.business.api_url' => (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
                'services.whatsapp.business.token' => (string) ($config['meta_access_token'] ?? ''),
                'services.whatsapp.business.phone_id' => (string) ($config['meta_phone_number_id'] ?? ''),
                'services.whatsapp.business.waba_id' => (string) ($config['meta_waba_id'] ?? ''),
                'services.whatsapp.zapi.api_url' => (string) ($config['zapi_api_url'] ?? 'https://api.z-api.io'),
                'services.whatsapp.zapi.token' => (string) ($config['zapi_token'] ?? ''),
                'services.whatsapp.zapi.client_token' => (string) ($config['zapi_client_token'] ?? ''),
                'services.whatsapp.zapi.instance_id' => (string) ($config['zapi_instance_id'] ?? ''),
            ]);

            $this->providerConfigResolver->applyWahaConfig($this->providerConfigResolver->resolveWahaConfig($config));
            return;
        }

        $globalProvider = function_exists('sysconfig')
            ? (string) sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : (string) config('services.whatsapp.provider', 'whatsapp_business');

        config([
            'services.whatsapp.force_runtime_provider' => true,
            'services.whatsapp.runtime_provider' => strtolower(trim($globalProvider)) ?: 'whatsapp_business',
            'services.whatsapp.provider' => $globalProvider,
            'services.whatsapp.business.api_url' => $this->resolveGlobalMetaValue(
                ['WHATSAPP_META_BASE_URL', 'WHATSAPP_BUSINESS_API_URL', 'WHATSAPP_API_URL'],
                (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0')
            ),
            'services.whatsapp.business.token' => $this->resolveGlobalMetaValue(
                ['WHATSAPP_META_TOKEN', 'WHATSAPP_BUSINESS_TOKEN', 'META_ACCESS_TOKEN', 'BOT_META_ACCESS_TOKEN', 'bot_meta_access_token'],
                (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))
            ),
            'services.whatsapp.business.phone_id' => $this->resolveGlobalMetaValue(
                ['WHATSAPP_META_PHONE_NUMBER_ID', 'WHATSAPP_BUSINESS_PHONE_ID', 'META_PHONE_NUMBER_ID', 'BOT_META_PHONE_NUMBER_ID', 'bot_meta_phone_number_id'],
                (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))
            ),
            'services.whatsapp.business.waba_id' => $this->resolveGlobalMetaValue(
                ['WHATSAPP_META_WABA_ID', 'WHATSAPP_BUSINESS_ACCOUNT_ID', 'META_WABA_ID', 'BOT_META_WABA_ID', 'bot_meta_waba_id'],
                (string) config('services.whatsapp.business.waba_id', '')
            ),
        ]);

        $this->providerConfigResolver->applyWahaConfig($this->providerConfigResolver->resolveWahaConfig());
    }

    private function resolveGlobalMetaValue(array $keys, string $fallback = ''): string
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

