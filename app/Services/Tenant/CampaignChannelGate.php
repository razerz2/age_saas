<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use DomainException;

class CampaignChannelGate
{
    /**
     * Returns the enabled channels for campaigns.
     *
     * Sanity:
     * - Returns only values from ["email", "whatsapp"].
     * - Returns [] when tenant providers are not configured.
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

    /**
     * Returns true only when tenant email provider is configured.
     */
    public function hasEmailProvider(): bool
    {
        $provider = TenantSetting::emailProvider();
        $driver = strtolower(trim((string) ($provider['driver'] ?? 'global')));

        if ($driver !== 'tenancy') {
            return false;
        }

        return $this->hasRequiredSettings(
            $provider,
            config('campaigns.channels.email.required_settings', [])
        );
    }

    /**
     * Returns true only when tenant WhatsApp provider is configured.
     */
    public function hasWhatsappProvider(): bool
    {
        $provider = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($provider['driver'] ?? 'global')));

        if ($driver !== 'tenancy') {
            return false;
        }

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
     * Validates whether all requested channels are enabled for tenant campaigns.
     *
     * Sanity:
     * - Throws when tenant has no available campaign channels.
     * - Throws when any requested channel is unavailable.
     *
     * @param array<int, string> $channels
     *
     * @throws DomainException
     */
    public function assertChannelsEnabled(array $channels): void
    {
        $available = $this->availableChannels();
        if ($available === []) {
            throw new DomainException('Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.');
        }

        $requestedChannels = $this->normalizeChannels($channels);
        foreach ($requestedChannels as $channel) {
            if (!in_array($channel, $available, true)) {
                throw new DomainException(
                    sprintf(
                        'Canal %s indisponível: configure a integração correspondente em Integrações.',
                        $this->channelLabel($channel)
                    )
                );
            }
        }
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
            default => $provider,
        };
    }

    private function channelLabel(string $channel): string
    {
        return match ($channel) {
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            default => strtoupper($channel),
        };
    }
}
