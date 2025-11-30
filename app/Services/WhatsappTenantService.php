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

            if ($provider['driver'] === 'tenancy') {
                // Usa API própria do tenant
                $response = Http::asJson()->post($provider['api_url'], [
                    'token' => $provider['api_token'],
                    'from' => $provider['sender'],
                    'to' => $phone,
                    'body' => $message,
                ]);

                Log::info('📤 WhatsApp enviado (tenant)', [
                    'to' => $phone,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return $response->successful();
            }

            // GLOBAL PROVIDER - usa WhatsAppService padrão
            $whatsappService = new WhatsAppService();
            return $whatsappService->sendMessage($phone, $message);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem WhatsApp', [
                'to' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

