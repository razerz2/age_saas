<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\PhoneNormalizer;

class WhatsAppBusinessProvider implements WhatsAppProviderInterface
{
    protected $apiUrl;
    protected $token;
    protected $phoneId;

    public function __construct()
    {
        // Tenta usar as novas configurações, se não existir, usa as legadas
        $this->apiUrl = config('services.whatsapp.business.api_url') 
            ?: config('services.whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->token = config('services.whatsapp.business.token') 
            ?: config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.business.phone_id') 
            ?: config('services.whatsapp.phone_id');
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->apiUrl}/{$this->phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->formatPhone($phone),
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message,
                    ],
                ]);

            Log::info('📤 WhatsApp Business enviado', [
                'provider' => 'whatsapp_business',
                'to' => $phone,
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem WhatsApp Business', [
                'provider' => 'whatsapp_business',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function formatPhone(string $phone): string
    {
        return PhoneNormalizer::formatForWhatsAppBusiness($phone);
    }
}

