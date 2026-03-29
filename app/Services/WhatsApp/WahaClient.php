<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
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

    public function getApiKeyHint(): string
    {
        return self::maskApiKey($this->apiKey);
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
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
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
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function sessionExists(): array
    {
        $statusResult = $this->getSessionStatus();
        $statusCode = (int) ($statusResult['status'] ?? 0);

        return [
            'ok' => !empty($statusResult['ok']),
            'exists' => $statusCode !== 404 && !empty($statusResult['ok']),
            'status' => $statusResult['status'] ?? null,
            'body' => $statusResult['body'] ?? null,
        ];
    }

    public function startSession(array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $endpoint = $this->baseUrl . '/api/sessions/start';
        $payload = array_merge([
            'name' => $this->session,
        ], $options);

        try {
            $response = $this->request()->post($endpoint, $payload);
            $body = $response->json();

            if ($body === null && $response->body() !== '') {
                $body = ['raw' => $response->body()];
            }

            if (!$response->successful()) {
                Log::warning('WAHA startSession request failed', [
                    'endpoint' => $endpoint,
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
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
            Log::warning('Falha ao iniciar sessao WAHA', [
                'base_url' => $this->baseUrl,
                'session' => $this->session,
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function startSessionByName(): array
    {
        return $this->sessionAction('start');
    }

    public function stopSessionByName(): array
    {
        return $this->sessionAction('stop');
    }

    public function restartSessionByName(): array
    {
        return $this->sessionAction('restart');
    }

    public function logoutSessionByName(): array
    {
        return $this->sessionAction('logout');
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,webhooks:array<int, array<string, mixed>>,webhook:array<string, mixed>|null}
     */
    public function getSessionWebhookConfig(): array
    {
        $statusResult = $this->getSessionStatus();
        $body = is_array($statusResult['body'] ?? null) ? $statusResult['body'] : [];
        $webhooks = $this->extractSessionWebhooks($body);

        return [
            'ok' => (bool) ($statusResult['ok'] ?? false),
            'status' => $statusResult['status'] ?? null,
            'body' => $body,
            'webhooks' => $webhooks,
            'webhook' => $webhooks[0] ?? null,
        ];
    }

    /**
     * @param array<int, string> $events
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null,method:string|null}
     */
    public function setSessionWebhook(string $url, array $events = ['message']): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
                'endpoint' => null,
                'method' => null,
            ];
        }

        $normalizedUrl = trim($url);
        if ($normalizedUrl === '' || filter_var($normalizedUrl, FILTER_VALIDATE_URL) === false) {
            return [
                'ok' => false,
                'status' => 422,
                'body' => ['message' => 'URL de webhook WAHA invalida.'],
                'endpoint' => null,
                'method' => null,
            ];
        }

        $normalizedEvents = $this->normalizeWebhookEvents($events);
        if ($normalizedEvents === []) {
            $normalizedEvents = ['message'];
        }

        $statusResult = $this->getSessionStatus();
        $sessionBody = is_array($statusResult['body'] ?? null) ? $statusResult['body'] : [];
        $baseConfig = data_get($sessionBody, 'config');
        if (!is_array($baseConfig)) {
            $baseConfig = data_get($sessionBody, 'session.config');
        }
        if (!is_array($baseConfig)) {
            $baseConfig = [];
        }

        $currentWebhook = $this->extractSessionWebhooks($sessionBody)[0] ?? [];
        $webhookPayload = $this->buildWebhookPayload($normalizedUrl, $normalizedEvents, $currentWebhook);
        $baseConfig['webhooks'] = [$webhookPayload];

        $payloadCandidates = [
            [
                'name' => $this->session,
                'config' => $baseConfig,
            ],
            [
                'config' => $baseConfig,
            ],
            [
                'name' => $this->session,
                'config' => [
                    'webhooks' => [$webhookPayload],
                ],
            ],
            [
                'name' => $this->session,
                'webhooks' => [$webhookPayload],
            ],
        ];

        $endpointCandidates = [
            $this->baseUrl . '/api/sessions/' . urlencode($this->session),
            $this->baseUrl . '/api/sessions/' . urlencode($this->session) . '/',
        ];
        $methodCandidates = ['put', 'post'];

        $lastResult = [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao configurar webhook WAHA.'],
            'endpoint' => null,
            'method' => null,
        ];

        foreach ($methodCandidates as $method) {
            foreach ($endpointCandidates as $endpoint) {
                foreach ($payloadCandidates as $payload) {
                    try {
                        $response = $this->request()->send(strtoupper($method), $endpoint, ['json' => $payload]);
                        $body = $response->json();
                        if ($body === null && $response->body() !== '') {
                            $body = ['raw' => $response->body()];
                        }

                        $result = [
                            'ok' => $response->successful(),
                            'status' => $response->status(),
                            'body' => $body,
                            'endpoint' => $endpoint,
                            'method' => strtoupper($method),
                        ];

                        if ($response->successful()) {
                            return $result;
                        }

                        $lastResult = $result;
                    } catch (\Throwable $e) {
                        $lastResult = [
                            'ok' => false,
                            'status' => null,
                            'body' => ['error' => $e->getMessage()],
                            'endpoint' => $endpoint,
                            'method' => strtoupper($method),
                        ];
                    }
                }
            }
        }

        // Fallback para versões em que o webhook é atualizado ao iniciar a sessão.
        $startResult = $this->startSession([
            'config' => [
                'webhooks' => [$webhookPayload],
            ],
        ]);

        if (!empty($startResult['ok'])) {
            return [
                'ok' => true,
                'status' => $startResult['status'] ?? null,
                'body' => $startResult['body'] ?? [],
                'endpoint' => $this->baseUrl . '/api/sessions/start',
                'method' => 'POST',
            ];
        }

        Log::warning('WAHA setSessionWebhook request failed', [
            'base_url' => $this->baseUrl,
            'session' => $this->session,
            'auth_header' => 'X-Api-Key',
            'api_key_hint' => $this->getApiKeyHint(),
            'status_code' => $lastResult['status'] ?? null,
            'endpoint' => $lastResult['endpoint'] ?? null,
            'method' => $lastResult['method'] ?? null,
            'body' => self::summarizeBody($lastResult['body'] ?? null),
            'start_fallback_status' => $startResult['status'] ?? null,
        ]);

        return $lastResult;
    }

    public function getSessionQrCode(string $format = 'image'): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $query = http_build_query(['format' => $format]);
        $endpoint = $this->baseUrl . '/api/' . urlencode($this->session) . '/auth/qr' . ($query !== '' ? '?' . $query : '');

        try {
            $response = $this->request()
                ->withHeaders(['Accept' => 'application/json'])
                ->get($endpoint);
            $body = $this->normalizeQrResponse($response);

            if (!$response->successful()) {
                Log::warning('WAHA qr request failed', [
                    'endpoint' => $endpoint,
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            Log::debug('WAHA qr payload normalized', [
                'endpoint' => $endpoint,
                'session' => $this->session,
                'status_code' => $response->status(),
                'content_type' => (string) $response->header('Content-Type', ''),
                'has_data' => trim((string) ($body['data'] ?? '')) !== '',
                'is_data_url' => !empty($body['is_data_url']),
                'mimetype' => $body['mimetype'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::warning('Falha ao obter QR da sessao WAHA', [
                'base_url' => $this->baseUrl,
                'session' => $this->session,
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeQrResponse(Response $response): array
    {
        $contentType = strtolower((string) $response->header('Content-Type', ''));
        $rawBody = (string) $response->body();
        $decoded = $response->json();

        $qrCandidate = is_array($decoded)
            ? $this->extractQrDataString($decoded)
            : null;

        $mimeType = is_array($decoded)
            ? $this->extractMimeType($decoded, $contentType)
            : $this->extractMimeType([], $contentType);

        // Caso o servidor retorne bytes de imagem diretamente.
        if ($qrCandidate === null && $rawBody !== '' && str_contains($contentType, 'image/')) {
            $qrCandidate = base64_encode($rawBody);
        }

        if ($qrCandidate === null && $rawBody !== '' && is_array($decoded) === false && $this->looksLikeBase64($rawBody)) {
            $qrCandidate = trim($rawBody);
        }

        $message = is_array($decoded)
            ? $this->extractMessage($decoded)
            : null;

        return [
            'mimetype' => $mimeType,
            'data' => $qrCandidate,
            'is_data_url' => is_string($qrCandidate) && str_starts_with($qrCandidate, 'data:'),
            'message' => $message,
        ];
    }

    private function extractQrDataString(array $payload): ?string
    {
        $priorityCandidates = [
            data_get($payload, 'data.qr'),
            data_get($payload, 'data.qrcode'),
            data_get($payload, 'data.qrCode'),
            data_get($payload, 'qr'),
            data_get($payload, 'qrcode'),
            data_get($payload, 'qrCode'),
            data_get($payload, 'data'),
            data_get($payload, 'image'),
            data_get($payload, 'src'),
            data_get($payload, 'base64'),
        ];

        foreach ($priorityCandidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $normalized = trim($candidate);
            if ($normalized === '') {
                continue;
            }

            if (str_starts_with($normalized, 'data:image/')) {
                return $normalized;
            }

            if ($this->looksLikeBase64($normalized)) {
                return $normalized;
            }
        }

        $flattened = Arr::dot($payload);
        foreach ($flattened as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $normalized = trim($value);
            if ($normalized === '') {
                continue;
            }

            $normalizedKey = strtolower((string) $key);
            $hasQrHint = str_contains($normalizedKey, 'qr')
                || str_contains($normalizedKey, 'qrcode')
                || str_ends_with($normalizedKey, '.data')
                || str_ends_with($normalizedKey, '.image')
                || str_ends_with($normalizedKey, '.src');

            if (!$hasQrHint) {
                continue;
            }

            if (str_starts_with($normalized, 'data:image/') || $this->looksLikeBase64($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    private function looksLikeBase64(string $value): bool
    {
        $normalized = preg_replace('/\s+/', '', trim($value)) ?? '';

        if ($normalized === '' || strlen($normalized) < 16) {
            return false;
        }

        if (strlen($normalized) % 4 !== 0) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9+\/=]+$/', $normalized) === 1;
    }

    /**
     * @param array<string, mixed> $body
     * @return array<int, array<string, mixed>>
     */
    private function extractSessionWebhooks(array $body): array
    {
        $candidates = [
            data_get($body, 'config.webhooks'),
            data_get($body, 'session.config.webhooks'),
            data_get($body, 'data.config.webhooks'),
            data_get($body, 'webhooks'),
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeWebhooksCandidate($candidate);
            if ($normalized !== []) {
                return $normalized;
            }
        }

        $flattened = Arr::dot($body);
        foreach ($flattened as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $normalizedKey = strtolower((string) $key);
            if (!str_ends_with($normalizedKey, '.webhooks') && $normalizedKey !== 'webhooks') {
                continue;
            }

            $normalized = $this->normalizeWebhooksCandidate($value);
            if ($normalized !== []) {
                return $normalized;
            }
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeWebhooksCandidate(mixed $candidate): array
    {
        if (!is_array($candidate)) {
            return [];
        }

        if (array_is_list($candidate)) {
            $result = [];
            foreach ($candidate as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalized = $this->normalizeWebhookEntry($item);
                if ($normalized['url'] === '') {
                    continue;
                }

                $result[] = $normalized;
            }

            return $result;
        }

        // Alguns retornos podem vir como objeto único.
        $single = $this->normalizeWebhookEntry($candidate);
        if ($single['url'] === '') {
            return [];
        }

        return [$single];
    }

    /**
     * @param array<string, mixed> $webhook
     * @return array<string, mixed>
     */
    private function normalizeWebhookEntry(array $webhook): array
    {
        $url = trim((string) (
            $webhook['url']
            ?? $webhook['webhook']
            ?? $webhook['webhookUrl']
            ?? $webhook['webhook_url']
            ?? ''
        ));

        $events = $this->normalizeWebhookEvents($webhook['events'] ?? []);
        $enabledRaw = $webhook['enabled'] ?? null;
        $enabled = is_bool($enabledRaw)
            ? $enabledRaw
            : ($enabledRaw === null ? null : filter_var((string) $enabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));

        $normalized = [
            'url' => $url,
            'events' => $events,
            'enabled' => $enabled,
        ];

        foreach (['hmac', 'retries', 'customHeaders'] as $optionalKey) {
            if (array_key_exists($optionalKey, $webhook)) {
                $normalized[$optionalKey] = $webhook[$optionalKey];
            }
        }

        return $normalized;
    }

    /**
     * @param array<int, string> $events
     * @param array<string, mixed> $baseWebhook
     * @return array<string, mixed>
     */
    private function buildWebhookPayload(string $url, array $events, array $baseWebhook = []): array
    {
        $payload = $baseWebhook;
        $payload['url'] = $url;
        $payload['events'] = $events;

        if (!array_key_exists('enabled', $payload)) {
            $payload['enabled'] = true;
        }

        return $payload;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeWebhookEvents(mixed $events): array
    {
        if (is_string($events)) {
            $events = array_map('trim', explode(',', $events));
        }

        if (!is_array($events)) {
            return [];
        }

        $normalized = [];
        foreach ($events as $event) {
            $value = trim((string) $event);
            if ($value === '') {
                continue;
            }

            $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
    }

    private function extractMimeType(array $payload, string $contentType): string
    {
        $candidates = [
            data_get($payload, 'mimetype'),
            data_get($payload, 'mimeType'),
            data_get($payload, 'mime'),
            data_get($payload, 'contentType'),
            $contentType,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $normalized = strtolower(trim($candidate));
            if ($normalized === '') {
                continue;
            }

            $normalized = explode(';', $normalized)[0];
            if (str_starts_with($normalized, 'image/')) {
                return $normalized;
            }
        }

        return 'image/png';
    }

    private function extractMessage(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'message'),
            data_get($payload, 'error'),
            data_get($payload, 'detail'),
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $message = trim($candidate);
            if ($message === '') {
                continue;
            }

            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($message, 'UTF-8', 'UTF-8');
            }

            return $message;
        }

        return null;
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
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            if (is_array($body) && isset($body['error'])) {
                Log::warning('❌ WAHA sendText error response', [
                    'endpoint' => $endpoint,
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
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
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
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
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
                    'status_code' => $response->status(),
                    'body' => self::summarizeBody($body),
                ]);
            }

            if (is_array($body) && isset($body['error'])) {
                Log::warning('❌ WAHA sendFile error response', [
                    'endpoint' => $endpoint,
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
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
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
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

    public static function maskApiKey(?string $apiKey): string
    {
        $value = trim((string) $apiKey);
        if ($value === '') {
            return '[empty]';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . '...' . substr($value, -4);
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

    private function sessionAction(string $action): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'WAHA nao esta configurado corretamente.'],
            ];
        }

        $endpoint = $this->baseUrl . '/api/sessions/' . urlencode($this->session) . '/' . trim($action);

        try {
            $response = $this->request()->post($endpoint);
            $body = $response->json();

            if ($body === null && $response->body() !== '') {
                $body = ['raw' => $response->body()];
            }

            if (!$response->successful()) {
                Log::warning('WAHA session action request failed', [
                    'endpoint' => $endpoint,
                    'action' => $action,
                    'base_url' => $this->baseUrl,
                    'session' => $this->session,
                    'auth_header' => 'X-Api-Key',
                    'api_key_hint' => $this->getApiKeyHint(),
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
            Log::warning('Falha ao executar acao de sessao WAHA', [
                'action' => $action,
                'base_url' => $this->baseUrl,
                'session' => $this->session,
                'auth_header' => 'X-Api-Key',
                'api_key_hint' => $this->getApiKeyHint(),
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }
}
