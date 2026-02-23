<?php

namespace App\Services\Providers;

class ProviderConfigResolver
{
    public function resolveWahaConfig(?array $tenantSettings = null): array
    {
        $settings = $tenantSettings ?? [];
        $driver = $settings['driver'] ?? 'global';
        if ($driver === 'tenancy') {
            return [
                'base_url' => (string) ($settings['waha_base_url'] ?? ''),
                'api_key' => (string) ($settings['waha_api_key'] ?? ''),
                'session' => (string) ($settings['waha_session'] ?? 'default'),
                'source' => 'tenant',
            ];
        }

        return [
            'base_url' => (string) sysconfig('WAHA_BASE_URL', config('services.whatsapp.waha.base_url', '')),
            'api_key' => (string) sysconfig('WAHA_API_KEY', config('services.whatsapp.waha.api_key', '')),
            'session' => (string) sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default')),
            'source' => 'global',
        ];
    }

    public function applyWahaConfig(array $config): void
    {
        config([
            'services.whatsapp.waha.base_url' => $config['base_url'] ?? '',
            'services.whatsapp.waha.api_key' => $config['api_key'] ?? '',
            'services.whatsapp.waha.session' => $config['session'] ?? 'default',
        ]);
    }
}
