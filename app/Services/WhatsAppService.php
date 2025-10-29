<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;
    protected $phoneId;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->token  = config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.phone_id');
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

            Log::info('📤 WhatsApp enviado', ['to' => $phone, 'status' => $response->status(), 'body' => $response->json()]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar mensagem WhatsApp', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatPhone(string $phone): string
    {
        // Ex: remove caracteres não numéricos e adiciona +55 se não tiver
        $digits = preg_replace('/\D/', '', $phone);
        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }
        return '+' . $digits;
    }
}
