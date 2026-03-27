<?php

namespace App\Services\Tenant\WhatsAppBot\Provider;

class WhatsAppBotRuntimeConfigApplier
{
    /**
     * @param array<string, mixed> $effectiveConfig
     */
    public function apply(array $effectiveConfig): void
    {
        $provider = strtolower(trim((string) ($effectiveConfig['provider'] ?? 'whatsapp_business')));
        if ($provider === '') {
            $provider = 'whatsapp_business';
        }

        config([
            'services.whatsapp.force_runtime_provider' => true,
            'services.whatsapp.runtime_provider' => $provider,
            'services.whatsapp.provider' => $provider,
            'services.whatsapp.business.api_url' => (string) config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v22.0'),
            'services.whatsapp.business.token' => (string) ($effectiveConfig['meta_access_token'] ?? ''),
            'services.whatsapp.business.phone_id' => (string) ($effectiveConfig['meta_phone_number_id'] ?? ''),
            'services.whatsapp.business.waba_id' => (string) ($effectiveConfig['meta_waba_id'] ?? ''),
            'services.whatsapp.zapi.api_url' => (string) ($effectiveConfig['zapi_api_url'] ?? 'https://api.z-api.io'),
            'services.whatsapp.zapi.token' => (string) ($effectiveConfig['zapi_token'] ?? ''),
            'services.whatsapp.zapi.client_token' => (string) ($effectiveConfig['zapi_client_token'] ?? ''),
            'services.whatsapp.zapi.instance_id' => (string) ($effectiveConfig['zapi_instance_id'] ?? ''),
            'services.whatsapp.waha.base_url' => (string) ($effectiveConfig['waha_base_url'] ?? ''),
            'services.whatsapp.waha.api_key' => (string) ($effectiveConfig['waha_api_key'] ?? ''),
            'services.whatsapp.waha.session' => (string) ($effectiveConfig['waha_session'] ?? 'default'),
        ]);
    }
}

