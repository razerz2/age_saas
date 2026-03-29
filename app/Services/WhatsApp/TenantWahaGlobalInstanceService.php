<?php

namespace App\Services\WhatsApp;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantWhatsAppGlobalInstance;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TenantWahaGlobalInstanceService
{
    public function __construct(
        private readonly TenantGlobalProviderCatalogService $tenantGlobalProviderCatalog
    ) {
    }

    /**
     * @param array<string, mixed>|null $providerSettings
     */
    public function usesTenantGlobalWaha(?array $providerSettings = null): bool
    {
        $settings = $providerSettings;
        if ($settings === null) {
            try {
                $settings = TenantSetting::whatsappProvider();
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        $settings = is_array($settings) ? $settings : [];
        $driver = strtolower(trim((string) ($settings['driver'] ?? 'global')));

        if ($driver !== 'global') {
            return false;
        }

        $globalProvider = $this->tenantGlobalProviderCatalog->resolveTenantGlobalProvider(
            (string) ($settings['global_provider'] ?? '')
        );

        return $globalProvider === TenantWhatsAppGlobalInstance::PROVIDER_WAHA;
    }

    /**
     * @param array<string, mixed>|null $providerSettings
     */
    public function resolveRuntimeSession(?array $providerSettings = null, ?Tenant $tenant = null): string
    {
        if (!$this->usesTenantGlobalWaha($providerSettings)) {
            return (string) sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default'));
        }

        $currentTenant = $tenant ?? Tenant::current();
        if (!$currentTenant) {
            return (string) sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default'));
        }

        try {
            $fallback = $this->resolveOperationalInstanceName($currentTenant);
        } catch (\Throwable $e) {
            Log::warning('Tenant without valid operational slug for WAHA runtime session', [
                'tenant_id' => $currentTenant->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return (string) sysconfig('WAHA_SESSION', config('services.whatsapp.waha.session', 'default'));
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            Log::warning('WAHA global instance table not available; using tenant operational slug as runtime session fallback', [
                'tenant_id' => $currentTenant->id ?? null,
                'tenant_slug' => $currentTenant->subdomain ?? null,
            ]);

            return $fallback;
        }

        try {
            $instance = TenantWhatsAppGlobalInstance::query()
                ->forTenant((string) $currentTenant->id)
                ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_WAHA)
                ->first();
        } catch (QueryException $e) {
            if ($this->isMissingRelationError($e)) {
                Log::warning('WAHA global instance table missing during runtime session resolution; using slug fallback', [
                    'tenant_id' => $currentTenant->id ?? null,
                    'tenant_slug' => $currentTenant->subdomain ?? null,
                ]);

                return $fallback;
            }

            throw $e;
        }

        if ($instance && trim((string) $instance->instance_name) !== '') {
            return trim((string) $instance->instance_name);
        }

        return $fallback;
    }

    public function ensureProvisionedForCurrentTenant(): array
    {
        return $this->ensureProvisionedForTenant(Tenant::current());
    }

    public function ensureProvisionedForTenant(?Tenant $tenant): array
    {
        if (!$tenant) {
            return [
                'ok' => false,
                'message' => 'Tenant nao encontrado para provisionar a sessao WAHA global.',
                'instance' => null,
            ];
        }

        try {
            $instanceName = $this->resolveOperationalInstanceName($tenant);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Slug operacional da clinica nao encontrado para provisionar a sessao WAHA.',
                'instance' => null,
            ];
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            return [
                'ok' => false,
                'message' => 'Estrutura WAHA global ainda nao aplicada. Execute as migrations da Platform para criar tenant_whatsapp_global_instances.',
                'instance' => null,
            ];
        }

        try {
            $instance = $this->upsertManagedRecord((string) $tenant->id, $instanceName);
        } catch (\RuntimeException $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'instance' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Falha inesperada ao vincular instancia WAHA global do tenant', [
                'tenant_id' => $tenant->id ?? null,
                'instance_name' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel preparar o vinculo da instancia WAHA desta clinica. Tente novamente.',
                'instance' => null,
            ];
        }

        $globalWaha = $this->resolveGlobalWahaApiConfig();
        if (!$globalWaha['valid']) {
            $error = 'Configuracao global do WAHA invalida na Platform. Defina WAHA_BASE_URL e WAHA_API_KEY antes de usar WAHA global no tenant.';
            $this->markError($instance, $error);

            return [
                'ok' => false,
                'message' => $error,
                'instance' => $instance,
            ];
        }

        $client = new WahaClient(
            (string) $globalWaha['base_url'],
            (string) $globalWaha['api_key'],
            $instanceName
        );

        $statusResult = $client->getSessionStatus();

        if ($this->isSessionMissing($statusResult)) {
            $startResult = $client->startSession();
            if (empty($startResult['ok'])) {
                $message = $this->buildCommunicationError(
                    'Falha ao criar a instancia WAHA do tenant.',
                    $startResult['status'] ?? null,
                    $startResult['body'] ?? null
                );
                $this->markError($instance, $message);

                return [
                    'ok' => false,
                    'message' => 'Nao foi possivel provisionar a sessao WAHA da clinica agora. Tente novamente em instantes.',
                    'instance' => $instance,
                ];
            }

            $statusAfterStart = $client->getSessionStatus();
            $statusResult = !empty($statusAfterStart['ok'])
                ? $statusAfterStart
                : [
                    'ok' => true,
                    'status' => $startResult['status'] ?? null,
                    'body' => $startResult['body'] ?? [],
                ];
        }

        if (empty($statusResult['ok'])) {
            $message = $this->buildCommunicationError(
                'Falha ao verificar a instancia WAHA do tenant.',
                $statusResult['status'] ?? null,
                $statusResult['body'] ?? null
            );
            $this->markError($instance, $message);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel validar a sessao WAHA da clinica agora. Tente novamente em instantes.',
                'instance' => $instance,
            ];
        }

        $remoteState = $this->extractRemoteState($statusResult['body'] ?? null);
        $instance->status = $remoteState !== '' ? strtolower($remoteState) : TenantWhatsAppGlobalInstance::STATUS_READY;
        $instance->last_error = null;
        $instance->managed_by_system = true;
        $instance->instance_name = $instanceName;
        $instance->save();

        return [
            'ok' => true,
            'message' => null,
            'instance' => $instance,
        ];
    }

    public function resolveOperationalInstanceName(Tenant $tenant): string
    {
        $instanceName = trim((string) $tenant->subdomain);

        if ($instanceName === '') {
            throw new \RuntimeException('Tenant sem slug/subdomain operacional para instancia WAHA.');
        }

        return $instanceName;
    }

    /**
     * @return array{base_url:string, api_key:string, valid:bool}
     */
    private function resolveGlobalWahaApiConfig(): array
    {
        $baseUrl = trim((string) sysconfig('WAHA_BASE_URL', config('services.whatsapp.waha.base_url', '')));
        $apiKey = trim((string) sysconfig('WAHA_API_KEY', config('services.whatsapp.waha.api_key', '')));

        $validUrl = $baseUrl !== '' && filter_var($baseUrl, FILTER_VALIDATE_URL) !== false;

        return [
            'base_url' => $baseUrl,
            'api_key' => $apiKey,
            'valid' => $validUrl && $apiKey !== '',
        ];
    }

    private function upsertManagedRecord(string $tenantId, string $instanceName): TenantWhatsAppGlobalInstance
    {
        $instance = TenantWhatsAppGlobalInstance::query()
            ->forTenant($tenantId)
            ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_WAHA)
            ->first();

        if (!$instance) {
            $instance = new TenantWhatsAppGlobalInstance([
                'tenant_id' => $tenantId,
                'provider' => TenantWhatsAppGlobalInstance::PROVIDER_WAHA,
            ]);
        }

        $instance->instance_name = $instanceName;
        $instance->managed_by_system = true;
        $instance->status = $instance->status ?: TenantWhatsAppGlobalInstance::STATUS_PENDING;

        try {
            $instance->save();
        } catch (QueryException $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            if ($this->isProviderInstanceConstraint($e)) {
                throw new \RuntimeException(
                    'Conflito de identificador operacional: ja existe outra instancia WAHA usando este nome de clinica.'
                );
            }

            if (!$this->isTenantProviderConstraint($e)) {
                throw $e;
            }

            // Corrida de gravacao: outro processo pode ter criado o mesmo vinculo.
            $existing = TenantWhatsAppGlobalInstance::query()
                ->forTenant($tenantId)
                ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_WAHA)
                ->first();

            if (!$existing) {
                throw $e;
            }

            $existing->instance_name = $instanceName;
            $existing->managed_by_system = true;
            $existing->status = $existing->status ?: TenantWhatsAppGlobalInstance::STATUS_PENDING;
            $existing->save();

            return $existing;
        }

        return $instance;
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());

        if ($sqlState === '23505') {
            return true;
        }

        return str_contains(strtolower($e->getMessage()), 'unique');
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

    private function isProviderInstanceConstraint(QueryException $e): bool
    {
        return str_contains(
            strtolower($e->getMessage()),
            'tenant_whatsapp_global_instances_provider_instance_unq'
        );
    }

    private function isTenantProviderConstraint(QueryException $e): bool
    {
        return str_contains(
            strtolower($e->getMessage()),
            'tenant_whatsapp_global_instances_tenant_provider_unq'
        );
    }

    private function markError(TenantWhatsAppGlobalInstance $instance, string $error): void
    {
        $instance->status = TenantWhatsAppGlobalInstance::STATUS_ERROR;
        $instance->last_error = $error;
        $instance->managed_by_system = true;
        $instance->save();

        Log::warning('Tenant WAHA global provisioning failed', [
            'tenant_id' => $instance->tenant_id,
            'provider' => $instance->provider,
            'instance_name' => $instance->instance_name,
            'error' => $error,
        ]);
    }

    private function isSessionMissing(array $statusResult): bool
    {
        $statusCode = (int) ($statusResult['status'] ?? 0);
        if ($statusCode === 404) {
            return true;
        }

        $body = is_array($statusResult['body'] ?? null) ? $statusResult['body'] : [];
        $message = strtolower(trim((string) ($body['error'] ?? $body['message'] ?? '')));

        return $message !== ''
            && str_contains($message, 'not found');
    }

    private function buildCommunicationError(string $context, mixed $status, mixed $body): string
    {
        $encodedBody = is_array($body)
            ? json_encode($body, JSON_UNESCAPED_UNICODE)
            : (string) ($body ?? '');

        if (strlen($encodedBody) > 1000) {
            $encodedBody = substr($encodedBody, 0, 1000) . '...';
        }

        return trim(sprintf(
            '%s [http_status=%s] [body=%s]',
            $context,
            $status ?? 'null',
            $encodedBody
        ));
    }

    private function extractRemoteState(mixed $body): string
    {
        if (!is_array($body)) {
            return '';
        }

        $state = trim((string) ($body['status'] ?? $body['state'] ?? ''));

        return strtoupper($state);
    }
}
