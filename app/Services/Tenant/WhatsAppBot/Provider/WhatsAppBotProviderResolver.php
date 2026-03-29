<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Exceptions\Tenant\WhatsAppBotConfigurationException;
use App\Services\Tenant\WhatsAppBotConfigService;

class WhatsAppBotProviderResolver
{
    public function __construct(
        private readonly WhatsAppBotConfigService $configService,
        private readonly WhatsAppBotProviderAdapterFactory $adapterFactory,
        private readonly WhatsAppBotRuntimeConfigApplier $runtimeConfigApplier
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveForCurrentTenant(bool $requireEnabled = true): array
    {
        $settings = $this->configService->getSettings();
        $enabled = (bool) ($settings['enabled'] ?? false);
        $effectiveConfig = $this->configService->resolveEffectiveProviderConfig($settings);
        $provider = $this->adapterFactory->normalizeProvider((string) ($effectiveConfig['provider'] ?? ''));

        if ($requireEnabled && !$enabled) {
            throw new WhatsAppBotConfigurationException('WhatsApp bot is disabled for this tenant.');
        }

        if (!$enabled && !$requireEnabled) {
            return [
                'enabled' => false,
                'provider_mode' => (string) ($settings['provider_mode'] ?? WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS),
                'provider' => $provider !== '' ? $provider : 'whatsapp_business',
                'settings' => $settings,
                'effective_config' => $effectiveConfig,
            ];
        }

        if (!$this->adapterFactory->isSupported($provider)) {
            throw new WhatsAppBotConfigurationException('WhatsApp bot provider is not supported: ' . $provider);
        }

        $this->assertProviderConfiguration($provider, $effectiveConfig);

        return [
            'enabled' => $enabled,
            'provider_mode' => (string) ($settings['provider_mode'] ?? WhatsAppBotConfigService::MODE_SHARED_WITH_NOTIFICATIONS),
            'provider' => $provider,
            'settings' => $settings,
            'effective_config' => $effectiveConfig,
        ];
    }

    /**
     * @param array<string, mixed> $resolved
     */
    public function applyRuntimeConfig(array $resolved): void
    {
        $this->runtimeConfigApplier->apply((array) ($resolved['effective_config'] ?? []));
    }

    /**
     * @param array<string, mixed> $effectiveConfig
     */
    private function assertProviderConfiguration(string $provider, array $effectiveConfig): void
    {
        if ($provider === 'whatsapp_business') {
            $this->assertRequired($effectiveConfig, ['meta_access_token', 'meta_phone_number_id'], $provider);
            return;
        }

        if ($provider === 'zapi') {
            $this->assertRequired($effectiveConfig, ['zapi_api_url', 'zapi_token', 'zapi_client_token', 'zapi_instance_id'], $provider);
            return;
        }

        if ($provider === 'waha') {
            $this->assertRequired($effectiveConfig, ['waha_base_url', 'waha_api_key', 'waha_session'], $provider);
            return;
        }

        if ($provider === 'evolution') {
            $this->assertRequired($effectiveConfig, ['evolution_base_url', 'evolution_api_key', 'evolution_instance'], $provider);
        }
    }

    /**
     * @param array<string, mixed> $effectiveConfig
     * @param array<int, string> $keys
     */
    private function assertRequired(array $effectiveConfig, array $keys, string $provider): void
    {
        $missing = [];

        foreach ($keys as $key) {
            if (trim((string) ($effectiveConfig[$key] ?? '')) === '') {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new WhatsAppBotConfigurationException(
                sprintf('WhatsApp bot provider "%s" has missing configuration keys: %s', $provider, implode(', ', $missing))
            );
        }
    }
}
