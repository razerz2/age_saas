<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaProvider implements WhatsAppProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $session;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.whatsapp.waha.base_url', ''), '/');
        $this->apiKey = (string) config('services.whatsapp.waha.api_key', '');
        $this->session = (string) config('services.whatsapp.waha.session', 'default');

        if (empty($this->baseUrl) || empty($this->apiKey) || empty($this->session)) {
            Log::warning('⚠️ WAHA não configurado corretamente', [
                'base_url_set' => !empty($this->baseUrl),
                'api_key_set' => !empty($this->apiKey),
                'session' => $this->session,
            ]);
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $formattedPhone = $this->formatPhone($phone);

            if (empty($this->baseUrl) || empty($this->apiKey) || empty($this->session)) {
                Log::error('❌ Tentativa de uso do WAHA sem configuração completa', [
                    'phone' => $formattedPhone,
                ]);
                return false;
            }

            // 1) Valida sessão
            $sessionUrl = $this->baseUrl . '/api/sessions/' . urlencode($this->session);

            $sessionResponse = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get($sessionUrl);

            $sessionData = $sessionResponse->json();

            Log::info('🔍 WAHA sessão verificada', [
                'url' => $sessionUrl,
                'status_code' => $sessionResponse->status(),
                'body' => $sessionData,
            ]);

            if (!$sessionResponse->successful() || !isset($sessionData['status']) || $sessionData['status'] !== 'WORKING') {
                Log::error('❌ Sessão WAHA não está WORKING, envio abortado', [
                    'session' => $this->session,
                    'status' => $sessionData['status'] ?? null,
                    'status_code' => $sessionResponse->status(),
                ]);
                return false;
            }

            // 2) Envia mensagem
            $sendUrl = $this->baseUrl . '/api/sendText';

            $payload = [
                'session' => $this->session,
                'chatId' => $formattedPhone,
                'text' => $message,
            ];

            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->post($sendUrl, $payload);

            $body = $response->json();

            Log::info('📤 WAHA resposta recebida', [
                'provider' => 'waha',
                'url' => $sendUrl,
                'to' => $formattedPhone,
                'status_code' => $response->status(),
                'body' => $body,
            ]);

            if (!$response->successful()) {
                Log::error('❌ Erro HTTP ao enviar mensagem WAHA', [
                    'status_code' => $response->status(),
                    'body' => $body,
                ]);
                return false;
            }

            // Considera sucesso se não houver campo de erro explícito
            if (is_array($body) && isset($body['error'])) {
                Log::error('❌ Erro na resposta WAHA', [
                    'error' => $body['error'],
                    'body' => $body,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('❌ Exceção ao enviar mensagem WAHA', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $phone,
            ]);
            return false;
        }
    }

    public function formatPhone(string $phone): string
    {
        // WAHA normalmente aceita o número em formato internacional sem +, ex: 5511999999999
        $digits = preg_replace('/\D/', '', $phone);
        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        return $digits;
    }
}
