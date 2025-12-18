<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\TenantAdmin;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\TenantProvisioner;
use App\Services\SystemSettingsService;
use App\Services\AsaasService;
use App\Mail\TenantAdminCredentialsMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantCreatorService
{
    /**
     * Centraliza a criação de um novo tenant, provisionamento de banco e envio de credenciais.
     */
    public function create(array $data): Tenant
    {
        // 🔒 Validação de Categoria de Plano vs Rede
        $networkId = $data['network_id'] ?? null;
        $planId = $data['plan_id'] ?? null;

        if ($planId) {
            $plan = Plan::findOrFail($planId);
            
            if ($networkId === null && $plan->category === Plan::CATEGORY_CONTRACTUAL) {
                abort(422, 'Planos contratuais são exclusivos para redes de clínicas.');
            }

            if ($networkId !== null && $plan->category !== Plan::CATEGORY_CONTRACTUAL) {
                abort(422, 'Tenants vinculados a uma rede devem usar plano contratual.');
            }
        }

        DB::beginTransaction();

        try {
            // 1. Preparar configuração de banco
            $dbConfig = TenantProvisioner::prepareDatabaseConfig(
                $data['legal_name'],
                $data['trade_name'] ?? null
            );

            // 2. Criar o Tenant
            $tenantData = array_merge($data, $dbConfig);
            
            // Se for rede, salvamos o plano diretamente no tenant para controle de acesso contratual
            if ($networkId && $planId) {
                $tenantData['plan_id'] = $planId;
            }

            $tenant = Tenant::create($tenantData);

            // 3. Criar Localização se houver dados
            if (!empty($data['endereco'])) {
                TenantLocalizacao::create([
                    'tenant_id'   => $tenant->id,
                    'endereco'    => $data['endereco'],
                    'n_endereco'  => $data['n_endereco'] ?? null,
                    'complemento' => $data['complemento'] ?? null,
                    'bairro'      => $data['bairro'] ?? null,
                    'cep'         => $data['cep'] ?? null,
                    'pais_id'     => $data['pais_id'] ?? 31, // Brasil fixo por padrão
                    'estado_id'   => $data['estado_id'] ?? null,
                    'cidade_id'   => $data['cidade_id'] ?? null,
                ]);
            }

            DB::commit();

            // 4. Provisionar Banco Físico
            $adminPassword = TenantProvisioner::createDatabase($tenant);

            // 5. Gerar e salvar credenciais do admin
            $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
            $adminEmail = "admin@{$sanitizedSubdomain}.com";
            $loginUrl = url("/t/{$tenant->subdomain}/login");

            TenantAdmin::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'email' => $adminEmail,
                    'password' => $adminPassword,
                    'login_url' => $loginUrl,
                    'name' => 'Administrador',
                    'password_visible' => true,
                ]
            );

            // 6. Enviar e-mail de credenciais
            $this->sendCredentials($tenant, $loginUrl, $adminEmail, $adminPassword);

            // 7. Configuração de Plano / Acesso
            if ($planId) {
                $plan = Plan::find($planId);
                
                if ($networkId) {
                    // 🏢 CASO REDE: Não cria assinatura, apenas aplica as regras de acesso ao banco do tenant
                    $planService = new TenantPlanService();
                    $planService->applyPlanRules($tenant, $plan);
                    Log::info("🏢 Tenant de Rede {$tenant->id}: Acesso configurado via plano contratual {$plan->name} (Sem Assinatura)");
                } else {
                    // 👤 CASO COMUM: Cria assinatura ativa (fluxo padrão de cobrança)
                    $this->createActiveSubscription($tenant, $planId);
                }
            }

            // 8. Sincronizar com Asaas (Apenas Cliente, sem cobrança se for rede)
            $this->syncAsaas($tenant);

            // Salvar senha na sessão para exibição imediata se for via web
            if (request()->hasSession()) {
                session()->flash('tenant_admin_password', $adminPassword);
            }

            return $tenant;

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('❌ Erro no TenantCreatorService', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Cria uma assinatura ativa para o tenant (Apenas para venda direta/comercial)
     */
    private function createActiveSubscription(Tenant $tenant, string $planId): void
    {
        try {
            $plan = Plan::findOrFail($planId);
            $startsAt = now();
            $endsAt = $startsAt->copy()->addMonths($plan->period_months ?? 1);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'status'    => 'active',
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
                'due_day'   => 1,
                'auto_renew' => true,
                'payment_method' => 'PIX',
            ]);

            // Aplica as regras no banco do tenant
            $planService = new TenantPlanService();
            $planService->applyPlanRules($tenant, $plan);

            Log::info("✅ Assinatura ativa criada para tenant {$tenant->id} no plano {$plan->name}");
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao criar assinatura automática: " . $e->getMessage());
        }
    }

    private function sendCredentials(Tenant $tenant, string $loginUrl, string $adminEmail, string $adminPassword): void
    {
        $systemSettingsService = new SystemSettingsService();
        if ($systemSettingsService->emailIsConfigured() && $tenant->email) {
            try {
                Mail::to($tenant->email)->send(
                    new TenantAdminCredentialsMail($tenant, $loginUrl, $adminEmail, $adminPassword)
                );
                Log::info("📧 Credenciais enviadas para {$tenant->email}");
            } catch (\Throwable $e) {
                Log::error("❌ Erro ao enviar email de credenciais: " . $e->getMessage());
            }
        }
    }

    private function syncAsaas(Tenant $tenant): void
    {
        try {
            // 🏢 Tenants vinculados a uma rede NUNCA devem sincronizar com o Asaas
            if ($tenant->network_id) {
                return;
            }
            // A sincronização real de cliente é chamada pelo Controller se necessário.
        } catch (\Throwable $e) {
            Log::error("❌ Erro na sincronia Asaas via Service: " . $e->getMessage());
        }
    }
}
