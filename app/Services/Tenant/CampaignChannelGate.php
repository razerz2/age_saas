<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Services\WhatsApp\TenantGlobalProviderCatalogService;
use DomainException;

class CampaignChannelGate
{
    private const MODE_NOTIFICATIONS = 'notifications';
    private const MODE_CUSTOM = 'custom';

    /**
     * Returns the enabled channels for campaigns.
     *
     * @return array<int, string>
     */
    public function availableChannels(): array
    {
        $channels = [];

        if ($this->hasEmailProvider()) {
            $channels[] = 'email';
        }

        if ($this->hasWhatsappProvider()) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    public function hasEmailProvider(): bool
    {
        $campaignConfig = TenantSetting::campaignEmailConfig();
        $mode = $this->normalizeMode((string) ($campaignConfig['mode'] ?? self::MODE_NOTIFICATIONS));

        if ($mode === self::MODE_CUSTOM) {
            return $this->hasRequiredSettings(
                $campaignConfig,
                config('campaigns.channels.email.required_settings', [])
            );
        }

        $provider = TenantSetting::emailProvider();
        $driver = strtolower(trim((string) ($provider['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            return $this->hasRequiredSettings(
                $provider,
                config('campaigns.channels.email.required_settings', [])
            );
        }

        if ($driver !== 'global') {
            return false;
        }

        return $this->hasGlobalEmailProvider();
    }

    public function hasWhatsappProvider(): bool
    {
        $campaignConfig = TenantSetting::campaignWhatsAppConfig();
        $mode = $this->normalizeMode((string) ($campaignConfig['mode'] ?? self::MODE_NOTIFICATIONS));

        if ($mode === self::MODE_CUSTOM) {
            return $this->hasTenancyWhatsappProvider($campaignConfig);
        }

        $provider = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($provider['driver'] ?? 'global')));

        if ($driver === 'tenancy') {
            return $this->hasTenancyWhatsappProvider($provider);
        }

        if ($driver !== 'global') {
            return false;
        }

        return $this->hasGlobalWhatsappProvider($provider);
    }

    /**
     * @param array<int, string> $channels
     */
    public function assertChannelsEnabled(array $channels): void
    {
        $available = $this->availableChannels();
        if ($available === []) {
            throw new DomainException('Nenhum canal de campanha está configurado. Configure os canais na aba Campanhas ou reutilize os canais de notificações.');
        }

        $requestedChannels = $this->normalizeChannels($channels);
        foreach ($requestedChannels as $channel) {
            if (!in_array($channel, $available, true)) {
                throw new DomainException(
                    sprintf(
                        'Canal %s indisponível para campanhas. Ajuste os canais na aba Campanhas.',
                        $this->channelLabel($channel)
                    )
                );
            }
        }
    }

    private function hasGlobalEmailProvider(): bool
    {
        $fromAddress = trim((string) config('mail.from.address', ''));
        if ($fromAddress === '') {
            return false;
        }

        $defaultMailer = strtolower(trim((string) config('mail.default', 'smtp')));
        if ($defaultMailer === 'tenant_smtp') {
            $defaultMailer = 'smtp';
        }

        if ($defaultMailer !== 'smtp') {
            return true;
        }

        $smtp = [
            'host' => (string) config('mail.mailers.smtp.host', ''),
            'port' => (string) config('mail.mailers.smtp.port', ''),
            'username' => (string) config('mail.mailers.smtp.username', ''),
            'password' => (string) config('mail.mailers.smtp.password', ''),
            'from_address' => $fromAddress,
        ];

        return $this->hasRequiredSettings(
            $smtp,
            config('campaigns.channels.email.required_settings', [])
        );
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function hasTenancyWhatsappProvider(array $provider): bool
    {
        $providerName = $this->normalizeWhatsappProvider((string) ($provider['provider'] ?? ''));
        $providerSettings = (array) config('campaigns.channels.whatsapp.providers', []);

        if ($providerName !== '' && array_key_exists($providerName, $providerSettings)) {
            $requiredSettings = $providerSettings[$providerName]['required_settings'] ?? [];
            return $this->hasRequiredSettings($provider, (array) $requiredSettings);
        }

        return $this->hasRequiredSettings(
            $provider,
            config('campaigns.channels.whatsapp.legacy_required_settings', [])
        );
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function hasGlobalWhatsappProvider(array $provider): bool
    {
        $catalog = app(TenantGlobalProviderCatalogService::class);
        $globalProvider = $catalog->resolveTenantGlobalProvider(
            (string) ($provider['global_provider'] ?? '')
        );

        if (!is_string($globalProvider) || trim($globalProvider) === '') {
            return false;
        }

        $globalProvider = $this->normalizeWhatsappProvider($globalProvider);

        return match ($globalProvider) {
            'whatsapp_business' => $this->hasRequiredSettings([
                'meta_access_token' => $this->resolveGlobalWhatsAppMetaValue([
                    'WHATSAPP_META_TOKEN',
                    'WHATSAPP_BUSINESS_TOKEN',
                    'META_ACCESS_TOKEN',
                    'BOT_META_ACCESS_TOKEN',
                    'bot_meta_access_token',
                ], (string) config('services.whatsapp.business.token', config('services.whatsapp.token', ''))),
                'meta_phone_number_id' => $this->resolveGlobalWhatsAppMetaValue([
                    'WHATSAPP_META_PHONE_NUMBER_ID',
                    'WHATSAPP_BUSINESS_PHONE_ID',
                    'META_PHONE_NUMBER_ID',
                    'BOT_META_PHONE_NUMBER_ID',
                    'bot_meta_phone_number_id',
                ], (string) config('services.whatsapp.business.phone_id', config('services.whatsapp.phone_id', ''))),
            ], ['meta_access_token', 'meta_phone_number_id']),
            'zapi' => $this->hasRequiredSettings([
                'zapi_api_url' => (string) config('services.whatsapp.zapi.api_url', ''),
                'zapi_token' => (string) config('services.whatsapp.zapi.token', ''),
                'zapi_client_token' => (string) config('services.whatsapp.zapi.client_token', ''),
                'zapi_instance_id' => (string) config('services.whatsapp.zapi.instance_id', ''),
            ], ['zapi_api_url', 'zapi_token', 'zapi_client_token', 'zapi_instance_id']),
            'waha' => $this->hasRequiredSettings([
                'waha_base_url' => $this->resolveSystemSetting('WAHA_BASE_URL', (string) config('services.whatsapp.waha.base_url', '')),
                'waha_api_key' => $this->resolveSystemSetting('WAHA_API_KEY', (string) config('services.whatsapp.waha.api_key', '')),
            ], ['waha_base_url', 'waha_api_key']),
            'evolution' => $this->hasRequiredSettings([
                'evolution_base_url' => $this->resolveEvolutionBaseUrl(),
                'evolution_api_key' => $this->resolveEvolutionApiKey(),
            ], ['evolution_base_url', 'evolution_api_key']),
            default => false,
        };
    }

    private function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        return in_array($mode, [self::MODE_NOTIFICATIONS, self::MODE_CUSTOM], true)
            ? $mode
            : self::MODE_NOTIFICATIONS;
    }

    private function resolveSystemSetting(string $key, string $fallback = ''): string
    {
        $value = function_exists('sysconfig')
            ? (string) sysconfig($key, $fallback)
            : $fallback;

        return trim($value);
    }

    private function resolveEvolutionBaseUrl(): string
    {
        $value = $this->resolveSystemSetting('EVOLUTION_BASE_URL', '');
        if ($value !== '') {
            return $value;
        }

        return $this->resolveSystemSetting(
            'EVOLUTION_API_URL',
            (string) config('services.whatsapp.evolution.base_url', '')
        );
    }

    private function resolveEvolutionApiKey(): string
    {
        $value = $this->resolveSystemSetting('EVOLUTION_API_KEY', '');
        if ($value !== '') {
            return $value;
        }

        return $this->resolveSystemSetting(
            'EVOLUTION_KEY',
            (string) config('services.whatsapp.evolution.api_key', '')
        );
    }

    /**
     * @param array<int, string> $keys
     */
    private function resolveGlobalWhatsAppMetaValue(array $keys, string $fallback = ''): string
    {
        foreach ($keys as $key) {
            $value = $this->resolveSystemSetting($key, '');
            if ($value !== '') {
                return $value;
            }
        }

        return trim($fallback);
    }

    /**
     * @param array<int, mixed> $source
     * @param array<int, mixed> $requiredSettings
     */
    private function hasRequiredSettings(array $source, array $requiredSettings): bool
    {
        foreach ($requiredSettings as $settingKey) {
            $value = trim((string) ($source[(string) $settingKey] ?? ''));
            if ($value === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $channels
     * @return array<int, string>
     */
    private function normalizeChannels(array $channels): array
    {
        $supported = (array) config('campaigns.channels.supported', ['email', 'whatsapp']);
        $normalized = [];

        foreach ($channels as $channel) {
            $channelValue = strtolower(trim((string) $channel));
            if ($channelValue === '') {
                continue;
            }

            if (!in_array($channelValue, $supported, true)) {
                $normalized[] = $channelValue;
                continue;
            }

            if (!in_array($channelValue, $normalized, true)) {
                $normalized[] = $channelValue;
            }
        }

        return $normalized;
    }

    private function normalizeWhatsappProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));

        return match ($provider) {
            'whatsapp-business', 'business' => 'whatsapp_business',
            'waha_gateway', 'waha-gateway', 'whatsapp_gateway', 'whatsapp-gateway',
            'waha_core', 'waha-core', 'whatsapp_waha', 'whatsapp-waha' => 'waha',
            'evolution_api', 'evolution-api', 'evolutionapi', 'evo_api', 'evo-api',
            'whatsapp_evolution', 'whatsapp-evolution' => 'evolution',
            default => $provider,
        };
    }

    private function channelLabel(string $channel): string
    {
        return match ($channel) {
            'email' => 'E-mail',
            'whatsapp' => 'WhatsApp',
            default => strtoupper($channel),
        };
    }
}
