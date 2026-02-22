<?php

namespace App\Services;

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
                        'services.whatsapp.waha.base_url' => $provider['waha_base_url'] ?? '',
                        'services.whatsapp.waha.api_key' => $provider['waha_api_key'] ?? '',
                        'services.whatsapp.waha.session' => $provider['waha_session'] ?? 'default',
                    ]);

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
