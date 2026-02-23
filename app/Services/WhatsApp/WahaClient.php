<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $session;
    protected int $timeout;

    public function __construct(string $baseUrl, string $apiKey, string $session = 'default', int $timeout = 12)
    {
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->apiKey = trim($apiKey);
        $this->session = trim($session) !== '' ? trim($session) : 'default';
        $this->timeout = $timeout;
    }

    public static function fromConfig(): self
    {
        return new self(
            (string) config('services.whatsapp.waha.base_url', ''),
            (string) config('services.whatsapp.waha.api_key', ''),
            (string) config('services.whatsapp.waha.session', 'default')
        );
    }

    public static function formatChatIdFromPhone(string $phone): string
    {
        $trimmed = trim($phone);
        if ($trimmed === '') {
            return '';
        }

        if (str_contains($trimmed, '@')) {
            return $trimmed;
        }

        $digits = preg_replace('/\D/', '', $trimmed);
        if ($digits === '') {
            return '';
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        return $digits . '@c.us';
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '' && $this->session !== '';
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getSession(): string
    {
        return $this->session;
    }

    public function getSessionStatus(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $endpoint = $this->baseUrl . '/api/sessions/' . urlencode($this->session);

        try {
            $response = $this->request()->get($endpoint);
            $body = $response->json();

            if ($body === null && $response->body() !== '') {
                $body = ['raw' => $response->body()];
            }

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::warning('⚠️ Falha ao consultar sessao WAHA', [
                'base_url' => $this->baseUrl,
                'session' => $this->session,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sendText(string $chatId, string $text): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $endpoint = $this->baseUrl . '/api/sendText';

        try {
            $response = $this->request()->post($endpoint, [
                'session' => $this->session,
                'chatId' => $chatId,
                'text' => $text,
            ]);

            $body = $response->json();
            if ($body === null && $response->body() !== '') {
                $body = ['raw' => $response->body()];
            }

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::warning('⚠️ Falha ao enviar mensagem WAHA', [
                'base_url' => $this->baseUrl,
                'session' => $this->session,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    protected function request(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withOptions(['verify' => app()->environment('local') ? false : true])
            ->withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ]);
    }
}
