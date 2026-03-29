<?php

namespace App\Services\WhatsApp;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantWhatsAppGlobalInstance;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TenantWahaGlobalOperationsService
{
    private const PROVIDER = TenantWhatsAppGlobalInstance::PROVIDER_WAHA;

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
                'message' => 'A operacao WAHA global nao esta disponivel para esta configuracao do tenant.',
            ];
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            return [
                'ok' => false,
                'http_status' => 503,
                'message' => 'Estrutura WAHA global indisponivel no momento.',
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
                    'message' => 'Estrutura WAHA global indisponivel no momento.',
                ];
            }

            throw $e;
        }

        if (!$instance) {
            return [
                'ok' => false,
                'http_status' => 403,
                'message' => 'Instancia WAHA global nao encontrada para este tenant.',
            ];
        }

        $instanceName = trim((string) $tenant->subdomain);
        if ($instanceName === '') {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Slug operacional da clinica nao encontrado para operar WAHA.',
            ];
        }

        if ($instance->instance_name !== $instanceName) {
            // O backend continua como fonte da verdade: corrige o nome para o slug atual.
            try {
                $instance->instance_name = $instanceName;
                $instance->managed_by_system = true;
                $instance->save();
            } catch (QueryException $e) {
                Log::warning('Conflito ao sincronizar nome da instancia WAHA global do tenant', [
                    'tenant_id' => $tenant->id ?? null,
                    'provider' => $instance->provider ?? null,
                    'instance_name' => $instanceName,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'ok' => false,
                    'http_status' => 409,
                    'message' => 'Conflito ao sincronizar a instancia WAHA da clinica. Contate o suporte.',
                ];
            }
        }

        $baseUrl = trim((string) sysconfig('WAHA_BASE_URL', config('services.whatsapp.waha.base_url', '')));
        $apiKey = trim((string) sysconfig('WAHA_API_KEY', config('services.whatsapp.waha.api_key', '')));

        if ($baseUrl === '' || filter_var($baseUrl, FILTER_VALIDATE_URL) === false || $apiKey === '') {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Configuracao global WAHA invalida na Platform. Defina WAHA_BASE_URL e WAHA_API_KEY.',
            ];
        }

        return [
            'ok' => true,
            'tenant' => $tenant,
            'settings' => $settings,
            'instance' => $instance,
            'instance_name' => $instanceName,
            'client' => new WahaClient($baseUrl, $apiKey, $instanceName),
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

        /** @var WahaClient $client */
        $client = $context['client'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];
        /** @var Tenant $tenant */
        $tenant = $context['tenant'];

        $statusResult = $client->getSessionStatus();
        $sessionBody = is_array($statusResult['body'] ?? null) ? $statusResult['body'] : [];
        $sessionStatus = strtoupper(trim((string) ($sessionBody['status'] ?? $sessionBody['state'] ?? 'UNKNOWN')));

        if (!empty($statusResult['ok'])) {
            $instance->status = strtolower($sessionStatus !== '' ? $sessionStatus : TenantWhatsAppGlobalInstance::STATUS_READY);
            $instance->last_error = null;
        } else {
            $instance->status = TenantWhatsAppGlobalInstance::STATUS_ERROR;
            $instance->last_error = $this->buildErrorMessage('Falha ao consultar status WAHA', $statusResult['status'] ?? null, $statusResult['body'] ?? null);
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
                'status' => $sessionStatus,
                'raw' => $sessionBody,
            ],
            'source' => 'global_system_managed',
            'supports' => [
                'start' => true,
                'restart' => true,
                'stop' => true,
                'logout' => true,
            ],
            'webhook' => $this->resolveWebhookStatus($client, $tenant),
            'qr' => null,
        ];

        if ($includeQr && $this->sessionNeedsQr($sessionStatus)) {
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

        /** @var WahaClient $client */
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
        $allowed = ['start', 'restart', 'stop', 'logout'];

        if (!in_array($normalizedAction, $allowed, true)) {
            return [
                'ok' => false,
                'http_status' => 422,
                'message' => 'Acao WAHA invalida.',
            ];
        }

        $context = $this->resolveCurrentTenantContext();
        if (empty($context['ok'])) {
            return $context;
        }

        /** @var WahaClient $client */
        $client = $context['client'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];

        $result = match ($normalizedAction) {
            'start' => $client->startSessionByName(),
            'restart' => $client->restartSessionByName(),
            'stop' => $client->stopSessionByName(),
            'logout' => $client->logoutSessionByName(),
        };

        if (empty($result['ok'])) {
            $error = $this->buildErrorMessage(
                'Falha ao executar acao WAHA: ' . $normalizedAction,
                $result['status'] ?? null,
                $result['body'] ?? null
            );

            $instance->status = TenantWhatsAppGlobalInstance::STATUS_ERROR;
            $instance->last_error = $error;
            $instance->managed_by_system = true;
            $instance->save();

            Log::warning('Tenant WAHA global action failed', [
                'tenant_id' => $instance->tenant_id,
                'action' => $normalizedAction,
                'instance_name' => $instance->instance_name,
                'error' => $error,
            ]);

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Nao foi possivel executar a acao WAHA no momento. Tente novamente.',
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

        /** @var WahaClient $client */
        $client = $context['client'];
        /** @var Tenant $tenant */
        $tenant = $context['tenant'];
        /** @var TenantWhatsAppGlobalInstance $instance */
        $instance = $context['instance'];

        $expectedUrl = $this->resolveExpectedWebhookUrl($tenant);
        $bindResult = $client->setSessionWebhook($expectedUrl, ['message']);

        if (empty($bindResult['ok'])) {
            $error = $this->buildErrorMessage(
                'Falha ao vincular webhook WAHA',
                $bindResult['status'] ?? null,
                $bindResult['body'] ?? null
            );
            $instance->last_error = $error;
            $instance->managed_by_system = true;
            $instance->save();

            Log::warning('Tenant WAHA global webhook bind failed', [
                'tenant_id' => $instance->tenant_id,
                'instance_name' => $instance->instance_name,
                'http_status' => $bindResult['status'] ?? null,
                'error' => $error,
            ]);

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Nao foi possivel vincular o webhook WAHA agora. Tente novamente em alguns instantes.',
            ];
        }

        $webhookStatus = $this->resolveWebhookStatus($client, $tenant);
        if (($webhookStatus['configured'] ?? false) !== true) {
            $instance->last_error = 'Webhook WAHA atualizado, mas ainda nao foi confirmado com a URL esperada.';
            $instance->managed_by_system = true;
            $instance->save();

            return [
                'ok' => false,
                'http_status' => 502,
                'message' => 'Webhook WAHA atualizado, mas a confirmacao final ainda nao foi concluida. Atualize o status em alguns segundos.',
            ];
        }

        $instance->last_error = null;
        $instance->managed_by_system = true;
        $instance->save();

        return [
            'ok' => true,
            'message' => 'Webhook WAHA vinculado com sucesso.',
            'status' => $this->status(true),
        ];
    }

    private function sessionNeedsQr(string $sessionStatus): bool
    {
        $status = strtoupper(trim($sessionStatus));

        return in_array($status, ['SCAN_QR_CODE', 'STARTING'], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchQrCodeInternal(WahaClient $client): array
    {
        $qrResult = $client->getSessionQrCode('image');
        $qrBody = is_array($qrResult['body'] ?? null) ? $qrResult['body'] : [];
        $qrData = trim((string) ($qrBody['data'] ?? ''));
        $qrMessage = trim((string) ($qrBody['message'] ?? ''));
        $qrMime = trim((string) ($qrBody['mimetype'] ?? 'image/png'));

        if (empty($qrResult['ok'])) {
            return [
                'ok' => false,
                'http_status' => $qrResult['status'] ?? null,
                'mimetype' => $qrMime !== '' ? $qrMime : 'image/png',
                'data' => $qrData !== '' ? $qrData : null,
                'message' => $qrMessage !== ''
                    ? $qrMessage
                    : 'QR Code ainda nao disponivel para esta sessao. Tente atualizar novamente em alguns segundos.',
            ];
        }

        if ($qrData === '') {
            return [
                'ok' => false,
                'http_status' => $qrResult['status'] ?? null,
                'mimetype' => $qrMime !== '' ? $qrMime : 'image/png',
                'data' => null,
                'message' => $qrMessage !== ''
                    ? $qrMessage
                    : 'QR Code ainda nao disponivel para esta sessao. Tente atualizar novamente em alguns segundos.',
            ];
        }

        return [
            'ok' => true,
            'http_status' => $qrResult['status'] ?? null,
            'mimetype' => $qrMime !== '' ? $qrMime : 'image/png',
            'data' => $qrData,
            'message' => null,
        ];
    }

    private function actionSuccessMessage(string $action): string
    {
        return match ($action) {
            'start' => 'Sessao WAHA iniciada com sucesso.',
            'restart' => 'Sessao WAHA reiniciada com sucesso.',
            'stop' => 'Sessao WAHA parada com sucesso.',
            'logout' => 'Sessao WAHA desconectada (logout) com sucesso.',
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
    private function resolveWebhookStatus(WahaClient $client, Tenant $tenant): array
    {
        $expectedUrl = $this->resolveExpectedWebhookUrl($tenant);
        $result = $client->getSessionWebhookConfig();
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
                'message' => 'Nao foi possivel consultar o webhook da sessao WAHA no momento.',
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
                ? 'Webhook WAHA configurado corretamente.'
                : ($currentUrl === ''
                    ? 'Webhook WAHA ainda nao configurado.'
                    : 'Webhook WAHA divergente da URL esperada.'),
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
