<?php

namespace App\Services\WhatsApp;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantWhatsAppGlobalInstance;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TenantEvolutionGlobalOperationsService
{
    private const PROVIDER = TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION;

    /**
     * @return array<string, mixed>
     */
    public function resolveCurrentTenantContext(): array
    {
        $tenant = Tenant::current();
        if (!$tenant) {
            return [
                'ok' => false,
                'http_status' => 404,
                'message' => 'Tenant nao encontrado.',
            ];
        }

        $settings = TenantSetting::whatsappProvider();
        $driver = strtolower(trim((string) ($settings['driver'] ?? 'global')));
        $globalProvider = app(TenantGlobalProviderCatalogService::class)->resolveTenantGlobalProvider(
            (string) ($settings['global_provider'] ?? '')
        );

        if ($driver !== 'global' || $globalProvider !== self::PROVIDER) {
            return [
                'ok' => false,
                'http_status' => 403,
                'message' => 'A operacao Evolution global nao esta disponivel para esta configuracao do tenant.',
            ];
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            return [
                'ok' => false,
                'http_status' => 503,
                'message' => 'Estrutura Evolution global indisponivel no momento.',
            ];
        }

        try {
            $instance = TenantWhatsAppGlobalInstance::query()
                ->forTenant((string) $tenant->id)
                ->forProvider(self::PROVIDER)
                ->first();
        } catch (QueryException $e) {
            if ($this->isMissingRelationError($e)) {
                return [
                    'ok' => false,
                    'http_status' => 503,
                    'message' => 'Estrutura Evolution global indisponivel no momento.',
                ];
            }

            throw $e;
        }

        if (!$instance) {
            return [
                'ok' => false,
                'http_status' => 403,
                'message' => 'Instancia Evolution global nao encontrada para este tenant.',
            ];
        }

        $instanceName = trim((string) $tenant->subdomain);
        if ($instanceName === '') {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Slug operacional da clinica nao encontrado para operar Evolution.',
            ];
        }

        if ($instance->instance_name !== $instanceName) {
            // O backend continua como fonte da verdade: corrige o nome para o slug atual.
            try {
                $instance->instance_name = $instanceName;
                $instance->managed_by_system = true;
                $instance->save();
            } catch (QueryException $e) {
                Log::warning('Conflito ao sincronizar nome da instancia Evolution global do tenant', [
                    'tenant_id' => $tenant->id ?? null,
                    'provider' => $instance->provider ?? null,
                    'instance_name' => $instanceName,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'ok' => false,
                    'http_status' => 409,
                    'message' => 'Conflito ao sincronizar a instancia Evolution da clinica. Contate o suporte.',
                ];
            }
        }

        $baseUrl = trim((string) sysconfig(
            'EVOLUTION_BASE_URL',
            sysconfig('EVOLUTION_API_URL', config('services.whatsapp.evolution.base_url', ''))
        ));
        $apiKey = trim((string) sysconfig(
            'EVOLUTION_API_KEY',
            sysconfig('EVOLUTION_KEY', config('services.whatsapp.evolution.api_key', ''))
        ));

        if ($baseUrl === '' || filter_var($baseUrl, FILTER_VALIDATE_URL) === false || $apiKey === '') {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Configuracao global Evolution invalida na Platform. Defina EVOLUTION_BASE_URL e EVOLUTION_API_KEY.',
            ];
        }

        return [
            'ok' => true,
            'tenant' => $tenant,
            'settings' => $settings,
            'instance' => $instance,
            'instance_name' => $instanceName,
            'client' => new EvolutionClient($baseUrl, $apiKey, $instanceName),
        ];
    }

    public function shouldShowTab(): bool
    {
        $context = $this->resolveCurrentTenantContext();

        return (bool) ($context['ok'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(bool $includeQr = false): array
    {
        $context = $this->resolveCurrentTenantContext();
        if (empty($context['ok'])) {
            return $context;
        }

        /** @var EvolutionClient $client */
        $client = $context['client'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];
        /** @var Tenant $tenant */
        $tenant = $context['tenant'];

        $statusResult = $client->getConnectionState();
        $sessionBody = is_array($statusResult['body'] ?? null) ? $statusResult['body'] : [];
        $normalizedState = $this->normalizeConnectionState((string) ($statusResult['state'] ?? ''));

        if (!empty($statusResult['ok'])) {
            $instance->status = strtolower($normalizedState['status']);
            $instance->last_error = null;
        } else {
            $instance->status = TenantWhatsAppGlobalInstance::STATUS_ERROR;
            $instance->last_error = $this->buildErrorMessage(
                'Falha ao consultar status Evolution',
                $statusResult['status'] ?? null,
                $statusResult['body'] ?? null
            );
        }
        $instance->managed_by_system = true;
        $instance->save();

        $payload = [
            'ok' => true,
            'instance' => [
                'provider' => (string) $instance->provider,
                'instance_name' => (string) $instance->instance_name,
                'managed_by_system' => (bool) $instance->managed_by_system,
                'status' => (string) $instance->status,
                'last_error' => $instance->last_error,
                'updated_at' => optional($instance->updated_at)?->toIso8601String(),
            ],
            'session' => [
                'ok' => (bool) ($statusResult['ok'] ?? false),
                'http_status' => $statusResult['status'] ?? null,
                'status' => $normalizedState['status'],
                'state' => $normalizedState['state'],
                'friendly_status' => $normalizedState['friendly'],
                'raw' => $sessionBody,
            ],
            'source' => 'global_system_managed',
            'supports' => [
                'start' => true,
                'restart' => true,
                'logout' => true,
            ],
            'webhook' => $this->resolveWebhookStatus($client, $tenant),
            'qr' => null,
        ];

        if ($includeQr && $this->stateLikelyNeedsQr((string) $normalizedState['status'])) {
            $payload['qr'] = $this->fetchQrCodeInternal($client);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function qrCode(): array
    {
        $context = $this->resolveCurrentTenantContext();
        if (empty($context['ok'])) {
            return $context;
        }

        /** @var EvolutionClient $client */
        $client = $context['client'];

        return [
            'ok' => true,
            'qr' => $this->fetchQrCodeInternal($client),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function executeAction(string $action): array
    {
        $normalizedAction = strtolower(trim($action));
        $allowed = ['start', 'restart', 'logout'];

        if (!in_array($normalizedAction, $allowed, true)) {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Acao Evolution invalida.',
            ];
        }

        $context = $this->resolveCurrentTenantContext();
        if (empty($context['ok'])) {
            return $context;
        }

        /** @var EvolutionClient $client */
        $client = $context['client'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];

        $result = match ($normalizedAction) {
            'start' => $client->connectInstance(),
            'restart' => $client->restartInstance(),
            'logout' => $client->logoutInstance(),
        };

        if (empty($result['ok'])) {
            $error = $this->buildErrorMessage(
                'Falha ao executar acao Evolution: ' . $normalizedAction,
                $result['status'] ?? null,
                $result['body'] ?? null
            );

            $instance->status = TenantWhatsAppGlobalInstance::STATUS_ERROR;
            $instance->last_error = $error;
            $instance->managed_by_system = true;
            $instance->save();

            Log::warning('Tenant Evolution global action failed', [
                'tenant_id' => $instance->tenant_id,
                'action' => $normalizedAction,
                'instance_name' => $instance->instance_name,
                'error' => $error,
            ]);

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Nao foi possivel executar a acao Evolution no momento. Tente novamente.',
            ];
        }

        return [
            'ok' => true,
            'message' => $this->actionSuccessMessage($normalizedAction),
            'status' => $this->status(true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function bindWebhook(): array
    {
        $context = $this->resolveCurrentTenantContext();
        if (empty($context['ok'])) {
            return $context;
        }

        /** @var EvolutionClient $client */
        $client = $context['client'];
        /** @var Tenant $tenant */
        $tenant = $context['tenant'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];

        $expectedUrl = $this->resolveExpectedWebhookUrl($tenant);
        $bindResult = $client->setWebhook($expectedUrl, ['MESSAGES_UPSERT']);

        if (empty($bindResult['ok'])) {
            $error = $this->buildErrorMessage(
                'Falha ao vincular webhook Evolution',
                $bindResult['status'] ?? null,
                $bindResult['body'] ?? null
            );

            $instance->last_error = $error;
            $instance->managed_by_system = true;
            $instance->save();

            Log::warning('Tenant Evolution global webhook bind failed', [
                'tenant_id' => $instance->tenant_id,
                'instance_name' => $instance->instance_name,
                'http_status' => $bindResult['status'] ?? null,
                'error' => $error,
            ]);

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Nao foi possivel vincular o webhook Evolution agora. Tente novamente em alguns instantes.',
            ];
        }

        $webhookStatus = $this->resolveWebhookStatus($client, $tenant);
        if (($webhookStatus['configured'] ?? false) !== true) {
            $instance->last_error = 'Webhook Evolution atualizado, mas ainda nao foi confirmado com a URL esperada.';
            $instance->managed_by_system = true;
            $instance->save();

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Webhook Evolution atualizado, mas a confirmacao final ainda nao foi concluida. Atualize o status em alguns segundos.',
            ];
        }

        $instance->last_error = null;
        $instance->managed_by_system = true;
        $instance->save();

        return [
            'ok' => true,
            'message' => 'Webhook Evolution vinculado com sucesso.',
            'status' => $this->status(true),
        ];
    }

    private function stateLikelyNeedsQr(string $status): bool
    {
        $normalized = strtoupper(trim($status));

        return in_array($normalized, [
            '',
            'CLOSE',
            'CLOSED',
            'DISCONNECTED',
            'OFFLINE',
            'NOT_CONNECTED',
            'CONNECTING',
            'STARTING',
            'PAIRING',
            'QRCODE',
            'SCAN_QR_CODE',
        ], true);
    }

    /**
     * @return array{status:string,state:string,friendly:string}
     */
    private function normalizeConnectionState(string $state): array
    {
        $normalized = strtolower(trim($state));

        return match ($normalized) {
            'open', 'connected', 'online', 'ready', 'working' => [
                'status' => 'OPEN',
                'state' => 'open',
                'friendly' => 'Conectado',
            ],
            'close', 'closed', 'disconnected', 'offline', 'not_connected' => [
                'status' => 'CLOSE',
                'state' => 'close',
                'friendly' => 'Desconectado',
            ],
            'connecting', 'starting' => [
                'status' => 'CONNECTING',
                'state' => 'connecting',
                'friendly' => 'Conectando',
            ],
            'qrcode', 'scan_qr_code', 'pairing' => [
                'status' => 'SCAN_QR_CODE',
                'state' => 'scan_qr_code',
                'friendly' => 'Aguardando leitura do QR Code',
            ],
            'error', 'failed', 'failure' => [
                'status' => 'ERROR',
                'state' => 'error',
                'friendly' => 'Erro de conexao',
            ],
            default => [
                'status' => strtoupper($normalized !== '' ? $normalized : 'UNKNOWN'),
                'state' => $normalized !== '' ? $normalized : 'unknown',
                'friendly' => 'Status desconhecido',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchQrCodeInternal(EvolutionClient $client): array
    {
        $result = $client->connectInstance();
        $body = is_array($result['body'] ?? null) ? $result['body'] : [];

        $normalized = $this->normalizeQrPayload($body);
        if (!$normalized['has_any']) {
            return [
                'ok' => false,
                'http_status' => $result['status'] ?? null,
                'mimetype' => 'image/png',
                'data' => null,
                'text_code' => null,
                'pairing_code' => null,
                'message' => $normalized['message'] !== ''
                    ? $normalized['message']
                    : 'QR Code ainda nao disponivel para esta instancia. Tente atualizar novamente em alguns segundos.',
            ];
        }

        return [
            'ok' => true,
            'http_status' => $result['status'] ?? null,
            'mimetype' => $normalized['mimetype'],
            'data' => $normalized['data'],
            'text_code' => $normalized['text_code'],
            'pairing_code' => $normalized['pairing_code'],
            'message' => $normalized['message'] !== '' ? $normalized['message'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array{has_any:bool,mimetype:string,data:?string,text_code:?string,pairing_code:?string,message:string}
     */
    private function normalizeQrPayload(array $body): array
    {
        $imageData = null;
        $mimeType = 'image/png';
        $textCode = null;
        $pairingCode = null;

        $message = trim((string) (
            $body['message']
            ?? $body['error']
            ?? data_get($body, 'response.message')
            ?? data_get($body, 'response.error')
            ?? ''
        ));

        $imageCandidates = [
            data_get($body, 'base64'),
            data_get($body, 'qrcode.base64'),
            data_get($body, 'qrcode'),
            data_get($body, 'qr.base64'),
            data_get($body, 'qr'),
            data_get($body, 'qrCode.base64'),
            data_get($body, 'qrCode'),
            data_get($body, 'response.base64'),
            data_get($body, 'response.qrcode.base64'),
            data_get($body, 'response.qrcode'),
            data_get($body, 'response.qrCode.base64'),
            data_get($body, 'response.qrCode'),
            data_get($body, 'response.qr'),
        ];

        foreach ($imageCandidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '') {
                continue;
            }

            if (str_starts_with($value, 'data:image/')) {
                $imageData = $value;
                $detected = $this->extractDataUriMimeType($value);
                if ($detected !== null) {
                    $mimeType = $detected;
                }
                break;
            }

            if ($this->looksLikeBase64($value)) {
                $imageData = $value;
                break;
            }
        }

        $textCandidates = [
            data_get($body, 'code'),
            data_get($body, 'qrcode.code'),
            data_get($body, 'qr.code'),
            data_get($body, 'response.code'),
            data_get($body, 'response.qrcode.code'),
            data_get($body, 'response.qr.code'),
        ];

        foreach ($textCandidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '' || $this->looksLikeBase64($value)) {
                continue;
            }

            $textCode = $value;
            break;
        }

        $pairingCandidates = [
            data_get($body, 'pairingCode'),
            data_get($body, 'pairing.code'),
            data_get($body, 'response.pairingCode'),
            data_get($body, 'response.pairing.code'),
        ];

        foreach ($pairingCandidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value === '') {
                continue;
            }

            $pairingCode = $value;
            break;
        }

        return [
            'has_any' => $imageData !== null || $textCode !== null || $pairingCode !== null,
            'mimetype' => $mimeType,
            'data' => $imageData,
            'text_code' => $textCode,
            'pairing_code' => $pairingCode,
            'message' => $message,
        ];
    }

    private function extractDataUriMimeType(string $value): ?string
    {
        if (!preg_match('/^data:([^;]+);base64,/i', $value, $matches)) {
            return null;
        }

        return trim((string) ($matches[1] ?? '')) ?: null;
    }

    private function looksLikeBase64(string $value): bool
    {
        $normalized = preg_replace('/\s+/', '', $value) ?? '';
        if ($normalized === '' || strlen($normalized) < 64) {
            return false;
        }

        if (strlen($normalized) % 4 !== 0) {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $normalized)) {
            return false;
        }

        return base64_decode($normalized, true) !== false;
    }

    private function actionSuccessMessage(string $action): string
    {
        return match ($action) {
            'start' => 'Instancia Evolution conectada com sucesso.',
            'restart' => 'Instancia Evolution reiniciada com sucesso.',
            'logout' => 'Instancia Evolution desconectada (logout) com sucesso.',
            default => 'Acao executada com sucesso.',
        };
    }

    private function buildErrorMessage(string $prefix, mixed $httpStatus, mixed $body): string
    {
        $encodedBody = is_array($body)
            ? json_encode($body, JSON_UNESCAPED_UNICODE)
            : (string) ($body ?? '');

        if (strlen($encodedBody) > 1000) {
            $encodedBody = substr($encodedBody, 0, 1000) . '...';
        }

        return sprintf('%s [http_status=%s] [body=%s]', $prefix, $httpStatus ?? 'null', $encodedBody);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveWebhookStatus(EvolutionClient $client, Tenant $tenant): array
    {
        $expectedUrl = $this->resolveExpectedWebhookUrl($tenant);
        $result = $client->getWebhook();
        $currentWebhook = is_array($result['webhook'] ?? null) ? $result['webhook'] : [];
        $currentUrl = trim((string) ($currentWebhook['url'] ?? ''));
        $configured = $currentUrl !== '' && $this->urlsAreEquivalent($currentUrl, $expectedUrl);

        if (empty($result['ok'])) {
            return [
                'ok' => false,
                'expected_url' => $expectedUrl,
                'current_url' => $currentUrl !== '' ? $currentUrl : null,
                'configured' => false,
                'needs_binding' => true,
                'message' => 'Nao foi possivel consultar o webhook da instancia Evolution no momento.',
                'http_status' => $result['status'] ?? null,
                'raw' => $result['body'] ?? null,
            ];
        }

        return [
            'ok' => true,
            'expected_url' => $expectedUrl,
            'current_url' => $currentUrl !== '' ? $currentUrl : null,
            'configured' => $configured,
            'needs_binding' => !$configured,
            'enabled' => $currentWebhook['enabled'] ?? null,
            'events' => $currentWebhook['events'] ?? [],
            'message' => $configured
                ? 'Webhook Evolution configurado corretamente.'
                : ($currentUrl === ''
                    ? 'Webhook Evolution ainda nao configurado.'
                    : 'Webhook Evolution divergente da URL esperada.'),
            'http_status' => $result['status'] ?? null,
            'raw' => $result['body'] ?? null,
        ];
    }

    private function resolveExpectedWebhookUrl(Tenant $tenant): string
    {
        return route('tenant.whatsapp-bot.webhook', [
            'slug' => (string) $tenant->subdomain,
            'provider' => self::PROVIDER,
        ]);
    }

    private function urlsAreEquivalent(string $left, string $right): bool
    {
        $normalize = static function (string $url): string {
            $trimmed = trim($url);
            if ($trimmed === '') {
                return '';
            }

            $parts = parse_url($trimmed);
            if (!is_array($parts)) {
                return rtrim($trimmed, '/');
            }

            $scheme = strtolower((string) ($parts['scheme'] ?? ''));
            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = rtrim((string) ($parts['path'] ?? ''), '/');
            $query = (string) ($parts['query'] ?? '');

            $normalized = ($scheme !== '' ? $scheme . '://' : '')
                . $host
                . (($parts['port'] ?? null) ? ':' . (string) $parts['port'] : '')
                . $path;

            if ($query !== '') {
                $normalized .= '?' . $query;
            }

            return rtrim($normalized, '/');
        };

        return $normalize($left) !== '' && $normalize($left) === $normalize($right);
    }

    private function isMissingRelationError(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
        if ($sqlState === '42P01') {
            return true;
        }

        return str_contains(strtolower($e->getMessage()), 'relation')
            && str_contains(strtolower($e->getMessage()), 'does not exist');
    }
}
