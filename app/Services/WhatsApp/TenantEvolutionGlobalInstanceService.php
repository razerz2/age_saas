<?php

namespace App\Services\WhatsApp;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantWhatsAppGlobalInstance;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TenantEvolutionGlobalInstanceService
{
    public function __construct(
        private readonly TenantGlobalProviderCatalogService $tenantGlobalProviderCatalog
    ) {
    }

    /**
     * @param array<string, mixed>|null $providerSettings
     */
    public function usesTenantGlobalEvolution(?array $providerSettings = null): bool
    {
        $settings = $providerSettings;
        if ($settings === null) {
            try {
                $settings = TenantSetting::whatsappProvider();
            } catch (\Throwable) {
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

        return $globalProvider === TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION;
    }

    /**
     * @param array<string, mixed>|null $providerSettings
     */
    public function resolveRuntimeInstance(?array $providerSettings = null, ?Tenant $tenant = null): string
    {
        if (!$this->usesTenantGlobalEvolution($providerSettings)) {
            $fallback = trim((string) sysconfig(
                'EVOLUTION_INSTANCE',
                sysconfig('EVOLUTION_INSTANCE_NAME', config('services.whatsapp.evolution.instance', 'default'))
            ));

            return $fallback !== '' ? $fallback : 'default';
        }

        $currentTenant = $tenant ?? Tenant::current();
        if (!$currentTenant) {
            return 'default';
        }

        try {
            $fallback = $this->resolveOperationalInstanceName($currentTenant);
        } catch (\Throwable $e) {
            Log::warning('Tenant without valid operational slug for Evolution runtime instance', [
                'tenant_id' => $currentTenant->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return 'default';
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            Log::warning('Evolution global instance table not available; using tenant operational slug as runtime instance fallback', [
                'tenant_id' => $currentTenant->id ?? null,
                'tenant_slug' => $currentTenant->subdomain ?? null,
            ]);

            return $fallback;
        }

        try {
            $instance = TenantWhatsAppGlobalInstance::query()
                ->forTenant((string) $currentTenant->id)
                ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION)
                ->first();
        } catch (QueryException $e) {
            if ($this->isMissingRelationError($e)) {
                Log::warning('Evolution global instance table missing during runtime instance resolution; using slug fallback', [
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
                'message' => 'Tenant nao encontrado para provisionar a instancia Evolution global.',
                'instance' => null,
            ];
        }

        try {
            $instanceName = $this->resolveOperationalInstanceName($tenant);
        } catch (\Throwable) {
            return [
                'ok' => false,
                'message' => 'Slug operacional da clinica nao encontrado para provisionar a instancia Evolution.',
                'instance' => null,
            ];
        }

        if (!TenantWhatsAppGlobalInstance::tableExists()) {
            return [
                'ok' => false,
                'message' => 'Estrutura Evolution global ainda nao aplicada. Execute as migrations da Platform para criar tenant_whatsapp_global_instances.',
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
            Log::warning('Falha inesperada ao vincular instancia Evolution global do tenant', [
                'tenant_id' => $tenant->id ?? null,
                'instance_name' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel preparar o vinculo da instancia Evolution desta clinica. Tente novamente.',
                'instance' => null,
            ];
        }

        $globalEvolution = $this->resolveGlobalEvolutionApiConfig();
        if (!$globalEvolution['valid']) {
            $error = 'Configuracao global do Evolution invalida na Platform. Defina EVOLUTION_BASE_URL e EVOLUTION_API_KEY antes de usar Evolution global no tenant.';
            $this->markError($instance, $error);

            return [
                'ok' => false,
                'message' => $error,
                'instance' => $instance,
            ];
        }

        $client = new EvolutionClient(
            (string) $globalEvolution['base_url'],
            (string) $globalEvolution['api_key'],
            $instanceName
        );

        $existsResult = $client->instanceExists();
        $isMissing = $client->isNotFoundResponse($existsResult['status'] ?? null, $existsResult['body'] ?? null);

        if (!$isMissing && empty($existsResult['ok'])) {
            $message = $this->buildCommunicationError(
                'Falha ao verificar a instancia Evolution do tenant.',
                $existsResult['status'] ?? null,
                $existsResult['body'] ?? null
            );
            $this->markError($instance, $message);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel validar a instancia Evolution da clinica agora. Tente novamente em instantes.',
                'instance' => $instance,
            ];
        }

        if ($isMissing) {
            $createResult = $client->createInstance($instanceName);
            $alreadyExists = $client->responseIndicatesAlreadyExists($createResult['body'] ?? null);

            if (empty($createResult['ok']) && !$alreadyExists) {
                $message = $this->buildCommunicationError(
                    'Falha ao criar a instancia Evolution do tenant.',
                    $createResult['status'] ?? null,
                    $createResult['body'] ?? null
                );
                $this->markError($instance, $message);

                return [
                    'ok' => false,
                    'message' => 'Nao foi possivel provisionar a instancia Evolution da clinica agora. Tente novamente em instantes.',
                    'instance' => $instance,
                ];
            }
        }

        $stateResult = $client->getConnectionState();
        if (empty($stateResult['ok'])) {
            $message = $this->buildCommunicationError(
                'Falha ao consultar status da instancia Evolution do tenant.',
                $stateResult['status'] ?? null,
                $stateResult['body'] ?? null
            );
            $this->markError($instance, $message);

            return [
                'ok' => false,
                'message' => 'Instancia Evolution vinculada, mas o status nao pode ser validado agora. Tente novamente em instantes.',
                'instance' => $instance,
            ];
        }

        $remoteState = strtolower(trim((string) ($stateResult['state'] ?? '')));
        $instance->status = $remoteState !== ''
            ? $remoteState
            : TenantWhatsAppGlobalInstance::STATUS_READY;
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
            throw new \RuntimeException('Tenant sem slug/subdomain operacional para instancia Evolution.');
        }

        return $instanceName;
    }

    /**
     * @return array{base_url:string, api_key:string, valid:bool}
     */
    private function resolveGlobalEvolutionApiConfig(): array
    {
        $baseUrl = trim((string) sysconfig(
            'EVOLUTION_BASE_URL',
            sysconfig('EVOLUTION_API_URL', config('services.whatsapp.evolution.base_url', ''))
        ));
        $apiKey = trim((string) sysconfig(
            'EVOLUTION_API_KEY',
            sysconfig('EVOLUTION_KEY', config('services.whatsapp.evolution.api_key', ''))
        ));

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
            ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION)
            ->first();

        if (!$instance) {
            $instance = new TenantWhatsAppGlobalInstance([
                'tenant_id' => $tenantId,
                'provider' => TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION,
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
                    'Conflito de identificador operacional: ja existe outra instancia Evolution usando este nome de clinica.'
                );
            }

            if (!$this->isTenantProviderConstraint($e)) {
                throw $e;
            }

            $existing = TenantWhatsAppGlobalInstance::query()
                ->forTenant($tenantId)
                ->forProvider(TenantWhatsAppGlobalInstance::PROVIDER_EVOLUTION)
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

        Log::warning('Tenant Evolution global provisioning failed', [
            'tenant_id' => $instance->tenant_id,
            'provider' => $instance->provider,
            'instance_name' => $instance->instance_name,
            'error' => $error,
        ]);
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
}

