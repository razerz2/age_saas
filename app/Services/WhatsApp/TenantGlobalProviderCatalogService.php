<?php

namespace App\Services\WhatsApp;

class TenantGlobalProviderCatalogService
{
    public const SYS_CONFIG_KEY = 'WHATSAPP_GLOBAL_ENABLED_PROVIDERS';

    /**
     * Providers currently supported as tenant-global providers.
     *
     * Keep this list non-official only.
     *
     * @var array<string, string>
     */
    private const SUPPORTED_PROVIDERS = [
        'waha' => 'WAHA',
        'evolution' => 'Evolution API',
    ];

    /**
     * Default enabled providers when system setting is not present yet.
     *
     * @var array<int, string>
     */
    private const DEFAULT_ENABLED_PROVIDERS = ['waha'];

    /**
     * @return array<string, string>
     */
    public function supportedProviderOptions(): array
    {
        return self::SUPPORTED_PROVIDERS;
    }

    /**
     * @return array<int, string>
     */
    public function supportedProviders(): array
    {
        return array_keys(self::SUPPORTED_PROVIDERS);
    }

    /**
     * @return array<int, string>
     */
    public function enabledProviders(): array
    {
        $raw = function_exists('sysconfig')
            ? sysconfig(self::SYS_CONFIG_KEY, null)
            : null;

        if ($raw === null) {
            return self::DEFAULT_ENABLED_PROVIDERS;
        }

        return $this->sanitizeProviders($this->decodeProviders($raw));
    }

    /**
     * @return array<string, string>
     */
    public function enabledProviderOptions(): array
    {
        $enabled = $this->enabledProviders();
        $supported = $this->supportedProviderOptions();
        $options = [];

        foreach ($enabled as $provider) {
            if (isset($supported[$provider])) {
                $options[$provider] = $supported[$provider];
            }
        }

        return $options;
    }

    public function normalizeProvider(?string $provider): string
    {
        $normalized = strtolower(trim((string) $provider));

        return match ($normalized) {
            'waha_core', 'waha-core', 'waha_gateway', 'waha-gateway',
            'whatsapp_waha', 'whatsapp-waha', 'whatsapp_gateway', 'whatsapp-gateway' => 'waha',
            'evolution_api', 'evolution-api', 'evolutionapi',
            'evo_api', 'evo-api', 'whatsapp_evolution', 'whatsapp-evolution' => 'evolution',
            default => $normalized,
        };
    }

    /**
     * @param  array<int, mixed>  $providers
     * @return array<int, string>
     */
    public function sanitizeProviders(array $providers): array
    {
        $supported = $this->supportedProviders();
        $result = [];

        foreach ($providers as $provider) {
            $normalized = $this->normalizeProvider((string) $provider);
            if ($normalized === '' || !in_array($normalized, $supported, true)) {
                continue;
            }

            if (!in_array($normalized, $result, true)) {
                $result[] = $normalized;
            }
        }

        return $result;
    }

    public function isEnabled(?string $provider): bool
    {
        $normalized = $this->normalizeProvider($provider);
        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, $this->enabledProviders(), true);
    }

    public function resolveTenantGlobalProvider(?string $tenantSelection): ?string
    {
        $normalized = $this->normalizeProvider($tenantSelection);
        if ($normalized !== '') {
            return $this->isEnabled($normalized) ? $normalized : null;
        }

        $enabled = $this->enabledProviders();
        return $enabled[0] ?? null;
    }

    /**
     * @param  array<int, mixed>  $providers
     */
    public function encodeProviders(array $providers): string
    {
        return json_encode(
            $this->sanitizeProviders($providers),
            JSON_UNESCAPED_UNICODE
        ) ?: '[]';
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeProviders(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw)) {
            return [];
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return array_map('trim', explode(',', $trimmed));
    }
}
