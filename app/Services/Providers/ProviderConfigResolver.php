<?php

namespace App\Services\Providers;

use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use App\Services\WhatsApp\TenantEvolutionGlobalInstanceService;
use App\Services\WhatsApp\TenantWahaGlobalInstanceService;

class ProviderConfigResolver
{
    public function applyUnofficialRuntimeConfigs(?array $tenantSettings = null): void
    {
        $settings = $tenantSettings ?? [];

        $this->applyWahaConfig($this->resolveWahaConfig($settings));
        $this->applyEvolutionConfig($this->resolveEvolutionConfig($settings));
    }

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

        $session = (string) sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default'));
        if ($settings !== []) {
            $catalog = app(TenantGlobalProviderCatalogService::class);
            $selectedGlobalProvider = $catalog->resolveTenantGlobalProvider(
                (string) ($settings['global_provider'] ?? '')
            );

            if ($selectedGlobalProvider === 'waha') {
                $session = app(TenantWahaGlobalInstanceService::class)->resolveRuntimeSession($settings);
            }
        }

        return [
            'base_url' => (string) sysconfig('WAHA_BASE_URL', config('services.whatsapp.waha.base_url', '')),
            'api_key' => (string) sysconfig('WAHA_API_KEY', config('services.whatsapp.waha.api_key', '')),
            'session' => $session,
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

    public function resolveEvolutionConfig(?array $tenantSettings = null): array
    {
        $settings = $tenantSettings ?? [];
        $driver = strtolower(trim((string) ($settings['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            $instance = trim((string) (
                $settings['evolution_instance']
                ?? $settings['evolution_instance_name']
                ?? 'default'
            ));

            return [
                'base_url' => trim((string) (
                    $settings['evolution_base_url']
                    ?? $settings['evolution_api_url']
                    ?? ''
                )),
                'api_key' => trim((string) (
                    $settings['evolution_api_key']
                    ?? $settings['evolution_apikey']
                    ?? ''
                )),
                'instance' => $instance !== '' ? $instance : 'default',
                'source' => 'tenant',
            ];
        }

        $instance = trim((string) sysconfig(
            'EVOLUTION_INSTANCE',
            sysconfig('EVOLUTION_INSTANCE_NAME', config('services.whatsapp.evolution.instance', 'default'))
        ));
        if ($settings !== []) {
            $catalog = app(TenantGlobalProviderCatalogService::class);
            $selectedGlobalProvider = $catalog->resolveTenantGlobalProvider(
                (string) ($settings['global_provider'] ?? '')
            );

            if ($selectedGlobalProvider === 'evolution') {
                $instance = app(TenantEvolutionGlobalInstanceService::class)->resolveRuntimeInstance($settings);
            }
        }

        return [
            'base_url' => trim((string) sysconfig(
                'EVOLUTION_BASE_URL',
                sysconfig('EVOLUTION_API_URL', config('services.whatsapp.evolution.base_url', ''))
            )),
            'api_key' => trim((string) sysconfig(
                'EVOLUTION_API_KEY',
                sysconfig('EVOLUTION_KEY', config('services.whatsapp.evolution.api_key', ''))
            )),
            'instance' => $instance !== '' ? $instance : 'default',
            'source' => 'global',
        ];
    }

    public function applyEvolutionConfig(array $config): void
    {
        config([
            'services.whatsapp.evolution.base_url' => $config['base_url'] ?? '',
            'services.whatsapp.evolution.api_key' => $config['api_key'] ?? '',
            'services.whatsapp.evolution.instance' => $config['instance'] ?? 'default',
        ]);
    }
}
