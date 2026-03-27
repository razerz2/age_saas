<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

use App\Exceptions\Tenant\WhatsAppBotConfigurationException;
use App\Services\Tenant\WhatsAppBot\Provider\Contracts\WhatsAppBotProviderAdapterInterface;

class WhatsAppBotProviderAdapterFactory
{
    public function normalizeProvider(string $provider): string
    {
        $normalized = strtolower(trim($provider));

        return match ($normalized) {
            'meta', 'business' => 'whatsapp_business',
            'z-api', 'z_api' => 'zapi',
            default => $normalized,
        };
    }

    public function isSupported(string $provider): bool
    {
        return in_array(
            $this->normalizeProvider($provider),
            ['whatsapp_business', 'zapi', 'waha'],
            true
        );
    }

    public function make(string $provider): WhatsAppBotProviderAdapterInterface
    {
        return match ($this->normalizeProvider($provider)) {
            'whatsapp_business' => app(WhatsAppBusinessBotProviderAdapter::class),
            'zapi' => app(ZApiBotProviderAdapter::class),
            'waha' => app(WahaBotProviderAdapter::class),
            default => throw new WhatsAppBotConfigurationException('Bot WhatsApp provider not supported: ' . $provider),
        };
    }
}

