<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;
    protected int $timeout;

    public function __construct(string $baseUrl, string $apiKey, string $instance = 'default', int $timeout = 12)
    {
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->apiKey = trim($apiKey);
        $this->instance = trim($instance) !== '' ? trim($instance) : 'default';
        $this->timeout = $timeout;
    }

    public static function fromConfig(): self
    {
        return new self(
            (string) config('services.whatsapp.evolution.base_url', ''),
            (string) config('services.whatsapp.evolution.api_key', ''),
            (string) config('services.whatsapp.evolution.instance', 'default')
        );
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '' && $this->instance !== '';
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getApiKeyHint(): string
    {
        return self::maskApiKey($this->apiKey);
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,state:string|null}
     */
    public function getConnectionState(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'state' => null,
            ];
        }

        $lastResult = null;
        $statePath = 'instance/connectionState/' . rawurlencode($this->instance);

        foreach ($this->endpointCandidates($statePath) as $endpoint) {
            try {
                $response = $this->request()->get($endpoint);
                $body = $this->normalizeResponseBody($response);
                $state = $this->extractConnectionState($body);

                if ($response->successful()) {
                    return [
                        'ok' => true,
                        'status' => $response->status(),
                        'body' => $body,
                        'state' => $state,
                    ];
                }

                $lastResult = [
                    'ok' => false,
                    'status' => $response->status(),
                    'body' => $body,
                    'state' => $state,
                ];
            } catch (\Throwable $e) {
                $lastResult = [
                    'ok' => false,
                    'status' => null,
                    'body' => ['error' => $e->getMessage()],
                    'state' => null,
                ];
            }
        }

        // Fallback adicional para ambientes que não expõem connectionState.
        $fetchPath = 'instance/fetchInstances?instanceName=' . rawurlencode($this->instance);
        foreach ($this->endpointCandidates($fetchPath) as $endpoint) {
            try {
                $response = $this->request()->get($endpoint);
                $body = $this->normalizeResponseBody($response);
                $state = $this->extractConnectionState($body);

                if ($response->successful()) {
                    return [
                        'ok' => true,
                        'status' => $response->status(),
                        'body' => $body,
                        'state' => $state,
                    ];
                }

                $lastResult = [
                    'ok' => false,
                    'status' => $response->status(),
                    'body' => $body,
                    'state' => $state,
                ];
            } catch (\Throwable $e) {
                $lastResult = [
                    'ok' => false,
                    'status' => null,
                    'body' => ['error' => $e->getMessage()],
                    'state' => null,
                ];
            }
        }

        Log::warning('Evolution connectionState request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'status_code' => $lastResult['status'] ?? null,
            'body' => self::summarizeBody($lastResult['body'] ?? null),
        ]);

        return $lastResult ?? [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao consultar estado da instancia Evolution.'],
            'state' => null,
        ];
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $result = $this->requestViaCandidates('instance/fetchInstances', ['get']);

        if (!empty($result['ok'])) {
            return $result;
        }

        Log::warning('Evolution connection test request failed', [
            'base_url' => $this->baseUrl,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $result['endpoint'] ?? null,
            'status_code' => $result['status'] ?? null,
            'body' => self::summarizeBody($result['body'] ?? null),
        ]);

        return $result;
    }

    /**
     * @return array{ok:bool,exists:bool,status:int|null,body:mixed,state:string|null}
     */
    public function instanceExists(): array
    {
        $statusResult = $this->getConnectionState();
        $statusCode = (int) ($statusResult['status'] ?? 0);
        $missing = $this->isNotFoundResponse($statusResult['status'] ?? null, $statusResult['body'] ?? null);

        return [
            'ok' => !empty($statusResult['ok']),
            'exists' => !$missing && ($statusCode === 0 || $statusCode < 400 || $statusCode === 401 || $statusCode === 403),
            'status' => $statusResult['status'] ?? null,
            'body' => $statusResult['body'] ?? null,
            'state' => $statusResult['state'] ?? null,
        ];
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function createInstance(?string $instanceName = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $name = trim((string) ($instanceName ?? $this->instance));
        if ($name === '') {
            $name = $this->instance;
        }

        $attempts = [
            [
                'path' => 'instance/create',
                'payload' => [
                    'instanceName' => $name,
                    'qrcode' => false,
                    'integration' => 'WHATSAPP-BAILEYS',
                ],
            ],
            [
                'path' => 'instance/create',
                'payload' => [
                    'instanceName' => $name,
                ],
            ],
        ];

        $lastResult = null;

        foreach ($attempts as $attempt) {
            foreach ($this->endpointCandidates((string) $attempt['path']) as $endpoint) {
                try {
                    $response = $this->request()->post($endpoint, (array) $attempt['payload']);
                    $body = $this->normalizeResponseBody($response);
                    $ok = $response->successful() && !$this->responseHasError($body);

                    $result = [
                        'ok' => $ok,
                        'status' => $response->status(),
                        'body' => $body,
                        'endpoint' => $endpoint,
                    ];

                    if ($ok) {
                        return $result;
                    }

                    $lastResult = $result;
                } catch (\Throwable $e) {
                    $lastResult = [
                        'ok' => false,
                        'status' => null,
                        'body' => ['error' => $e->getMessage()],
                        'endpoint' => $endpoint,
                    ];
                }
            }
        }

        Log::warning('Evolution createInstance request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $name,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $lastResult['endpoint'] ?? null,
            'status_code' => $lastResult['status'] ?? null,
            'body' => self::summarizeBody($lastResult['body'] ?? null),
        ]);

        return $lastResult ?? [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao criar instancia Evolution.'],
            'endpoint' => null,
        ];
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function connectInstance(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $path = 'instance/connect/' . rawurlencode($this->instance);
        $result = $this->requestViaCandidates($path, ['get']);

        if (!empty($result['ok'])) {
            return $result;
        }

        Log::warning('Evolution connectInstance request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $result['endpoint'] ?? null,
            'status_code' => $result['status'] ?? null,
            'body' => self::summarizeBody($result['body'] ?? null),
        ]);

        return $result;
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function restartInstance(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $path = 'instance/restart/' . rawurlencode($this->instance);
        $result = $this->requestViaCandidates($path, ['put', 'post']);

        if (!empty($result['ok'])) {
            return $result;
        }

        Log::warning('Evolution restartInstance request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $result['endpoint'] ?? null,
            'status_code' => $result['status'] ?? null,
            'body' => self::summarizeBody($result['body'] ?? null),
        ]);

        return $result;
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function logoutInstance(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $path = 'instance/logout/' . rawurlencode($this->instance);
        $result = $this->requestViaCandidates($path, ['delete', 'post']);

        if (!empty($result['ok'])) {
            return $result;
        }

        Log::warning('Evolution logoutInstance request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $result['endpoint'] ?? null,
            'status_code' => $result['status'] ?? null,
            'body' => self::summarizeBody($result['body'] ?? null),
        ]);

        return $result;
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null,webhook:array<string,mixed>|null}
     */
    public function getWebhook(): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
                'webhook' => null,
            ];
        }

        $path = 'webhook/find/' . rawurlencode($this->instance);
        $result = $this->requestViaCandidates($path, ['get']);
        $webhook = $this->extractWebhookInfo($result['body'] ?? null);

        if (!empty($result['ok'])) {
            $result['webhook'] = $webhook;
            return $result;
        }

        Log::warning('Evolution getWebhook request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $result['endpoint'] ?? null,
            'status_code' => $result['status'] ?? null,
            'body' => self::summarizeBody($result['body'] ?? null),
        ]);

        $result['webhook'] = $webhook;
        return $result;
    }

    /**
     * @param array<int, string> $events
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null,webhook:array<string,mixed>|null}
     */
    public function setWebhook(string $url, array $events = ['MESSAGES_UPSERT']): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
                'webhook' => null,
            ];
        }

        $normalizedUrl = trim($url);
        if ($normalizedUrl === '' || filter_var($normalizedUrl, FILTER_VALIDATE_URL) === false) {
            return [
                'ok' => false,
                'status' => 422,
                'body' => ['message' => 'URL de webhook Evolution invalida.'],
                'endpoint' => null,
                'webhook' => null,
            ];
        }

        $normalizedEvents = $this->normalizeWebhookEvents($events);
        if ($normalizedEvents === []) {
            $normalizedEvents = ['MESSAGES_UPSERT'];
        }

        $payloadCandidates = [
            [
                'enabled' => true,
                'url' => $normalizedUrl,
                'webhookByEvents' => true,
                'webhookBase64' => false,
                'events' => $normalizedEvents,
            ],
            [
                'enabled' => true,
                'url' => $normalizedUrl,
                'webhook_by_events' => true,
                'webhook_base64' => false,
                'events' => $normalizedEvents,
            ],
        ];

        $lastResult = [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao configurar webhook Evolution.'],
            'endpoint' => null,
        ];

        $path = 'webhook/set/' . rawurlencode($this->instance);
        foreach ($payloadCandidates as $payload) {
            $result = $this->requestViaCandidates($path, ['post'], $payload);
            $lastResult = $result;

            if (!empty($result['ok'])) {
                $result['webhook'] = $this->extractWebhookInfo($result['body'] ?? null);
                return $result;
            }
        }

        Log::warning('Evolution setWebhook request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $lastResult['endpoint'] ?? null,
            'status_code' => $lastResult['status'] ?? null,
            'body' => self::summarizeBody($lastResult['body'] ?? null),
        ]);

        $lastResult['webhook'] = $this->extractWebhookInfo($lastResult['body'] ?? null);
        return $lastResult;
    }

    /**
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    public function sendText(string $phone, string $text): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Evolution API nao esta configurada corretamente.'],
                'endpoint' => null,
            ];
        }

        $number = preg_replace('/\D+/', '', trim($phone)) ?? '';
        if ($number === '') {
            return [
                'ok' => false,
                'status' => null,
                'body' => ['message' => 'Telefone invalido para envio Evolution.'],
                'endpoint' => null,
            ];
        }

        $attempts = [
            [
                'path' => 'message/sendText/' . rawurlencode($this->instance),
                'payload' => [
                    'number' => $number,
                    'text' => $text,
                ],
            ],
            [
                'path' => 'message/sendText/' . rawurlencode($this->instance),
                'payload' => [
                    'number' => $number,
                    'textMessage' => [
                        'text' => $text,
                    ],
                ],
            ],
            [
                'path' => 'chat/sendMessage/' . rawurlencode($this->instance),
                'payload' => [
                    'number' => $number,
                    'message' => $text,
                ],
            ],
        ];

        $lastResult = null;

        foreach ($attempts as $attempt) {
            foreach ($this->endpointCandidates((string) $attempt['path']) as $endpoint) {
                try {
                    $response = $this->request()->post($endpoint, (array) $attempt['payload']);
                    $body = $this->normalizeResponseBody($response);
                    $hasError = $this->responseHasError($body);
                    $ok = $response->successful() && !$hasError;

                    $result = [
                        'ok' => $ok,
                        'status' => $response->status(),
                        'body' => $body,
                        'endpoint' => $endpoint,
                    ];

                    if ($ok) {
                        return $result;
                    }

                    $lastResult = $result;
                } catch (\Throwable $e) {
                    $lastResult = [
                        'ok' => false,
                        'status' => null,
                        'body' => ['error' => $e->getMessage()],
                        'endpoint' => $endpoint,
                    ];
                }
            }
        }

        Log::warning('Evolution sendText request failed', [
            'base_url' => $this->baseUrl,
            'instance' => $this->instance,
            'api_key_hint' => $this->getApiKeyHint(),
            'endpoint' => $lastResult['endpoint'] ?? null,
            'status_code' => $lastResult['status'] ?? null,
            'body' => self::summarizeBody($lastResult['body'] ?? null),
        ]);

        return $lastResult ?? [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao enviar mensagem Evolution.'],
            'endpoint' => null,
        ];
    }

    protected function request(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withOptions(['verify' => app()->environment('local') ? false : true])
            ->withHeaders([
                'apikey' => $this->apiKey,
                'Accept' => 'application/json',
            ]);
    }

    /**
     * @return array<int, string>
     */
    private function endpointCandidates(string $path): array
    {
        $relativePath = ltrim($path, '/');
        return [$this->baseUrl . '/' . $relativePath];
    }

    private function normalizeResponseBody(Response $response): mixed
    {
        $body = $response->json();
        if ($body === null && trim((string) $response->body()) !== '') {
            return ['raw' => (string) $response->body()];
        }

        return $body;
    }

    private function responseHasError(mixed $body): bool
    {
        if (!is_array($body)) {
            return false;
        }

        $error = trim((string) ($body['error'] ?? ''));
        if ($error !== '') {
            return true;
        }

        $status = strtolower(trim((string) ($body['status'] ?? '')));
        if (in_array($status, ['error', 'failed', 'failure'], true)) {
            return true;
        }

        return array_key_exists('success', $body) && $body['success'] === false;
    }

    /**
     * @param array<int, string> $methods
     * @return array{ok:bool,status:int|null,body:mixed,endpoint:string|null}
     */
    private function requestViaCandidates(string $path, array $methods, array $payload = []): array
    {
        $lastResult = [
            'ok' => false,
            'status' => null,
            'body' => ['message' => 'Falha ao comunicar com Evolution API.'],
            'endpoint' => null,
        ];

        foreach ($methods as $method) {
            $normalizedMethod = strtolower(trim($method));
            if (!in_array($normalizedMethod, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                continue;
            }

            foreach ($this->endpointCandidates($path) as $endpoint) {
                try {
                    $response = $this->request()->send(strtoupper($normalizedMethod), $endpoint, [
                        'json' => $payload,
                        'query' => $normalizedMethod === 'get' ? $payload : [],
                    ]);
                    $body = $this->normalizeResponseBody($response);
                    $ok = $response->successful() && !$this->responseHasError($body);

                    $result = [
                        'ok' => $ok,
                        'status' => $response->status(),
                        'body' => $body,
                        'endpoint' => $endpoint,
                    ];

                    if ($ok) {
                        return $result;
                    }

                    $lastResult = $result;
                } catch (\Throwable $e) {
                    $lastResult = [
                        'ok' => false,
                        'status' => null,
                        'body' => ['error' => $e->getMessage()],
                        'endpoint' => $endpoint,
                    ];
                }
            }
        }

        return $lastResult;
    }

    public function responseIndicatesAlreadyExists(mixed $body): bool
    {
        if (!is_array($body)) {
            return false;
        }

        $text = strtolower(trim((string) (
            $body['message']
            ?? $body['error']
            ?? data_get($body, 'response.message')
            ?? data_get($body, 'response.error')
            ?? ''
        )));

        if ($text === '') {
            return false;
        }

        return str_contains($text, 'already exists')
            || str_contains($text, 'instance already')
            || str_contains($text, 'ja existe')
            || str_contains($text, 'conflict');
    }

    private function extractConnectionState(mixed $body): ?string
    {
        if (!is_array($body)) {
            return null;
        }

        $candidates = [
            data_get($body, 'instance.state'),
            data_get($body, 'instance.status'),
            data_get($body, 'state'),
            data_get($body, 'status'),
            data_get($body, 'response.instance.state'),
            data_get($body, 'response.instance.status'),
        ];

        foreach ($candidates as $candidate) {
            $state = strtolower(trim((string) $candidate));
            if ($state !== '') {
                return $state;
            }
        }

        if (!array_is_list($body)) {
            return null;
        }

        foreach ($body as $item) {
            if (!is_array($item)) {
                continue;
            }

            $instanceName = strtolower(trim((string) data_get($item, 'instance.instanceName')));
            if ($instanceName !== strtolower($this->instance)) {
                continue;
            }

            $state = strtolower(trim((string) (
                data_get($item, 'instance.state')
                ?? data_get($item, 'instance.status')
            )));
            if ($state !== '') {
                return $state;
            }
        }

        $flattened = Arr::dot($body);
        foreach ($flattened as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $normalizedKey = strtolower((string) $key);
            if (!str_contains($normalizedKey, 'state') && !str_contains($normalizedKey, 'status')) {
                continue;
            }

            $state = strtolower(trim($value));
            if ($state !== '') {
                return $state;
            }
        }

        return null;
    }

    public function isNotFoundResponse(mixed $status, mixed $body): bool
    {
        if ((int) ($status ?? 0) === 404) {
            return true;
        }

        if (!is_array($body)) {
            return false;
        }

        $text = strtolower(trim((string) (
            $body['message']
            ?? $body['error']
            ?? data_get($body, 'response.message')
            ?? data_get($body, 'response.error')
            ?? ''
        )));

        if ($text === '') {
            return false;
        }

        return str_contains($text, 'not found')
            || str_contains($text, 'instance not')
            || str_contains($text, 'nao encontrada')
            || str_contains($text, 'não encontrada');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractWebhookInfo(mixed $body): ?array
    {
        if (!is_array($body)) {
            return null;
        }

        $url = trim((string) (
            data_get($body, 'webhook.webhook.url')
            ?? data_get($body, 'webhook.url')
            ?? data_get($body, 'response.webhook.url')
            ?? data_get($body, 'response.webhook.webhook.url')
            ?? data_get($body, 'url')
            ?? ''
        ));

        $events = data_get($body, 'webhook.webhook.events')
            ?? data_get($body, 'webhook.events')
            ?? data_get($body, 'response.webhook.events')
            ?? data_get($body, 'events')
            ?? [];

        $enabledRaw = data_get($body, 'webhook.webhook.enabled')
            ?? data_get($body, 'webhook.enabled')
            ?? data_get($body, 'response.webhook.enabled')
            ?? data_get($body, 'enabled');

        $enabled = null;
        if (is_bool($enabledRaw)) {
            $enabled = $enabledRaw;
        } elseif ($enabledRaw !== null) {
            $enabled = filter_var((string) $enabledRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $instanceName = trim((string) (
            data_get($body, 'webhook.instanceName')
            ?? data_get($body, 'instance.instanceName')
            ?? data_get($body, 'instanceName')
            ?? $this->instance
        ));

        if ($url === '' && $events === [] && $enabled === null) {
            return null;
        }

        return [
            'instance' => $instanceName !== '' ? $instanceName : $this->instance,
            'url' => $url,
            'events' => $this->normalizeWebhookEvents($events),
            'enabled' => $enabled,
        ];
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
            $value = strtoupper(trim((string) $event));
            if ($value === '') {
                continue;
            }
            $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
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
}
