<?php

namespace App\Services;

use App\Services\Providers\ProviderConfigResolver;
use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappTenantService
{
    /**
     * Envia mensagem WhatsApp usando API do tenant ou global
     */
    public static function send($phone, $message): bool
    {
        try {
            $provider = TenantSetting::whatsappProvider();
            $resolver = new ProviderConfigResolver();

            if (($provider['driver'] ?? 'global') === 'tenancy') {
                if (!empty($provider['provider'])) {
                    config([
                        'services.whatsapp.provider' => $provider['provider'],
                        'services.whatsapp.business.api_url' => config('services.whatsapp.business.api_url', 'https://graph.facebook.com/v18.0'),
                        'services.whatsapp.business.token' => $provider['meta_access_token'] ?? '',
                        'services.whatsapp.business.phone_id' => $provider['meta_phone_number_id'] ?? '',
                        'services.whatsapp.zapi.api_url' => $provider['zapi_api_url'] ?? 'https://api.z-api.io',
                        'services.whatsapp.zapi.token' => $provider['zapi_token'] ?? '',
                        'services.whatsapp.zapi.client_token' => $provider['zapi_client_token'] ?? '',
                        'services.whatsapp.zapi.instance_id' => $provider['zapi_instance_id'] ?? '',
                    ]);

                    $resolver->applyWahaConfig($resolver->resolveWahaConfig($provider));

                    $whatsappService = new WhatsAppService();
                    return $whatsappService->sendMessage($phone, $message);
                }

                // Fallback legado para tenants com configuracao antiga
                if (!empty($provider['api_url']) && !empty($provider['api_token'])) {
                    $response = Http::asJson()->post($provider['api_url'], [
                        'token' => $provider['api_token'],
                        'from' => $provider['sender'],
                        'to' => $phone,
                        'body' => $message,
                    ]);

                    Log::info('WhatsApp enviado (tenant legado)', [
                        'to' => $phone,
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);

                    return $response->successful();
                }
            }

            $globalProvider = sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'));
            config([
                'services.whatsapp.provider' => $globalProvider,
            ]);
            $resolver->applyWahaConfig($resolver->resolveWahaConfig());

            // GLOBAL PROVIDER - usa WhatsAppService padrao
            $whatsappService = new WhatsAppService();
            return $whatsappService->sendMessage($phone, $message);
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mensagem WhatsApp', [
                'to' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
