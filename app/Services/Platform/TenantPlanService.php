<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\PlanAccessRule;
use App\Models\Tenant\TenantPlanLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantPlanService
{
    /**
     * Aplica as regras de acesso de um plano ao banco de dados do tenant.
     */
    public function applyPlanRules(Tenant $tenant, Plan $plan): bool
    {
        try {
            // 1. Busca a regra de acesso do plano
            $rule = PlanAccessRule::where('plan_id', $plan->id)
                ->with('features')
                ->first();

            if (!$rule) {
                Log::warning("⚠️ Regra de acesso não encontrada para o plano: {$plan->name}");
                return false;
            }

            // 2. Prepara os dados das features permitidas
            $allowedFeatures = $rule->features->where('pivot.allowed', true)->pluck('name')->toArray();

            $limitsData = [
                'max_admin_users' => $rule->max_admin_users,
                'max_common_users' => $rule->max_common_users,
                'max_doctors' => $rule->max_doctors,
                'allowed_features' => $allowedFeatures,
            ];

            // 3. Configura a conexão temporária com o banco do tenant
            config([
                'database.connections.tenant_sync.driver' => 'pgsql',
                'database.connections.tenant_sync.host' => $tenant->db_host,
                'database.connections.tenant_sync.port' => $tenant->db_port,
                'database.connections.tenant_sync.database' => $tenant->db_name,
                'database.connections.tenant_sync.username' => $tenant->db_username,
                'database.connections.tenant_sync.password' => $tenant->db_password,
            ]);

            DB::purge('tenant_sync');

            // 4. Salva os limites no banco do tenant
            // Usamos uma conexão específica para evitar bagunça com a conexão 'tenant' do Spatie
            DB::connection('tenant_sync')->table('tenant_plan_limits')->truncate();
            DB::connection('tenant_sync')->table('tenant_plan_limits')->insert(array_merge($limitsData, [
                'id' => \Illuminate\Support\Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            Log::info("✅ Regras de acesso aplicadas ao tenant: {$tenant->trade_name}", [
                'plan' => $plan->name,
                'limits' => $limitsData,
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error("❌ Erro ao aplicar regras de acesso ao tenant {$tenant->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}

