<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;

class CampaignTemplateProviderResolver
{
    private const OFFICIAL_PROVIDER = 'whatsapp_business';

    /**
     * @var array<int, string>
     */
    private const UNOFFICIAL_PROVIDERS = ['zapi', 'waha', 'evolution'];

    public function __construct(
        private readonly TenantGlobalProviderCatalogService $tenantGlobalProviderCatalogService
    ) {
    }

    public function resolveWhatsAppProvider(): string
    {
        $campaignConfig = TenantSetting::campaignWhatsAppConfig();
        $mode = $this->normalizeMode((string) ($campaignConfig['mode'] ?? 'notifications'));

        if ($mode === 'custom') {
            return $this->normalizeProvider((string) ($campaignConfig['provider'] ?? self::OFFICIAL_PROVIDER));
        }

        return $this->resolveNotificationsWhatsAppProvider();
    }

    public function isOfficialWhatsApp(): bool
    {
        return $this->resolveWhatsAppProvider() === self::OFFICIAL_PROVIDER;
    }

    public function isUnofficialWhatsApp(): bool
    {
        return in_array($this->resolveWhatsAppProvider(), self::UNOFFICIAL_PROVIDERS, true);
    }

    private function resolveNotificationsWhatsAppProvider(): string
    {
        $notificationMode = strtolower(trim((string) TenantSetting::get('notifications.whatsapp.provider_mode', '')));
        if (!in_array($notificationMode, ['global', 'tenancy'], true)) {
            $notificationMode = strtolower(trim((string) TenantSetting::get('whatsapp.driver', 'global')));
        }

        if (!in_array($notificationMode, ['global', 'tenancy'], true)) {
            $notificationMode = 'global';
        }

        if ($notificationMode === 'tenancy') {
            $provider = trim((string) TenantSetting::get('notifications.whatsapp.provider', ''));
            if ($provider === '') {
                $provider = trim((string) (TenantSetting::whatsappProvider()['provider'] ?? ''));
            }

            return $this->normalizeProvider($provider);
        }

        $whatsAppConfig = TenantSetting::whatsappProvider();
        $globalProviderSelection = trim((string) ($whatsAppConfig['global_provider'] ?? ''));
        if ($globalProviderSelection === '') {
            $globalProviderSelection = trim((string) TenantSetting::get('notifications.whatsapp.provider', ''));
        }

        $globalProvider = $this->tenantGlobalProviderCatalogService->resolveTenantGlobalProvider($globalProviderSelection);

        return $this->normalizeProvider((string) ($globalProvider ?? ''));
    }

    private function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        return in_array($mode, ['notifications', 'custom'], true) ? $mode : 'notifications';
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        $provider = match ($provider) {
            'whatsapp-business', 'business', 'meta' => self::OFFICIAL_PROVIDER,
            'waha_core', 'waha-core', 'waha_gateway', 'waha-gateway',
            'whatsapp_waha', 'whatsapp-waha', 'whatsapp_gateway', 'whatsapp-gateway' => 'waha',
            'evolution_api', 'evolution-api', 'evolutionapi',
            'evo_api', 'evo-api', 'whatsapp_evolution', 'whatsapp-evolution' => 'evolution',
            default => $provider,
        };

        if ($provider === self::OFFICIAL_PROVIDER || in_array($provider, self::UNOFFICIAL_PROVIDERS, true)) {
            return $provider;
        }

        return self::OFFICIAL_PROVIDER;
    }
}

