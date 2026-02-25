<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\PhoneNormalizer;

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

        $normalized = PhoneNormalizer::normalizeWahaBrPhone($trimmed);
        return $normalized === '' ? '' : $normalized . '@c.us';
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

            if (!$response->successful()) {
                Log::warning('❌ WAHA session status request failed', [
                    'endpoint' => $endpoint,
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
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

            if (!$response->successful()) {
                Log::warning('❌ WAHA sendText request failed', [
                    'endpoint' => $endpoint,
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            if (is_array($body) && isset($body['error'])) {
                Log::warning('❌ WAHA sendText error response', [
                    'endpoint' => $endpoint,
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
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

    public function sendFileFromUrl(string $chatId, string $fileUrl, ?string $caption = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $endpoint = $this->baseUrl . '/api/sendFile';
        $payload = [
            'session' => $this->session,
            'chatId' => $chatId,
            'file' => [
                'url' => $fileUrl,
            ],
        ];

        if ($caption !== null && trim($caption) !== '') {
            $payload['caption'] = $caption;
        }

        try {
            $response = $this->request()->post($endpoint, $payload);

            $body = $response->json();
            if ($body === null && $response->body() !== '') {
                $body = ['raw' => $response->body()];
            }

            if (!$response->successful()) {
                Log::warning('❌ WAHA sendFile request failed', [
                    'endpoint' => $endpoint,
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            if (is_array($body) && isset($body['error'])) {
                Log::warning('❌ WAHA sendFile error response', [
                    'endpoint' => $endpoint,
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::warning('⚠️ Falha ao enviar arquivo WAHA', [
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

    private static function summarizeBody(mixed $body, int $limit = 800): string
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        $body = (string) ($body ?? '');
        if (strlen($body) <= $limit) {
            return $body;
        }

        return substr($body, 0, $limit) . '...';
    }
}
