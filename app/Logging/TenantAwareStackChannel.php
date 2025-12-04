<?php

namespace App\Logging;

use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Log\LogManager;

/**
 * Stack customizado que separa logs de tenants do log principal
 * 
 * - Se houver tenant ativo: usa apenas 'dynamic_tenant' (não escreve no log principal)
 * - Se não houver tenant: usa apenas 'single' (escreve no log principal)
 */
class TenantAwareStackChannel
{
    public function __invoke(array $config)
    {
        $logManager = app(LogManager::class);
        
        // Detecta se há um tenant ativo
        $hasTenant = $this->hasActiveTenant();
        
        if ($hasTenant) {
            // Tenant ativo: usa apenas o canal do tenant (não escreve no log principal)
            return $logManager->channel('dynamic_tenant');
        } else {
            // Sem tenant: usa apenas o log principal
            return $logManager->channel('single');
        }
    }

    /**
     * Verifica se há um tenant ativo
     */
    protected function hasActiveTenant(): bool
    {
        // 1️⃣ Prioridade: Tenant ativo pelo Spatie
        if (\class_exists(Tenant::class)) {
            $current = Tenant::current();
            if ($current) {
                return true;
            }
        }

        // 2️⃣ Tenant registrado no container manualmente
        if (\app()->bound('currentTenant')) {
            $tenant = \app('currentTenant');
            if ($tenant) {
                return true;
            }
        }

        return false;
    }
}

