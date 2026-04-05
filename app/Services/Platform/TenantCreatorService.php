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
    private const BRAZIL_COUNTRY_ID = 31;

    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp
    ) {
    }

    /**
     * Centraliza a criação de um novo tenant, provisionamento de banco e envio de credenciais.
     */
    public function create(array $data): Tenant
    {
        // Compatibilidade temporaria: `tenants.plan_id` ainda pode chegar do fluxo de criacao.
        // A fonte comercial oficial permanece sendo `subscriptions.plan_id`.
        $planId = $data['plan_id'] ?? null;
        unset($data['network_id']);

        DB::beginTransaction();

        try {
            // 1. Preparar configuração de banco
            $dbConfig = TenantProvisioner::prepareDatabaseConfig(
                $data['legal_name'],
                $data['trade_name'] ?? null
            );

            // 2. Criar o Tenant
            $tenantData = array_merge($data, $dbConfig);

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
                    'pais_id'     => self::BRAZIL_COUNTRY_ID,
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
                // Sempre cria uma assinatura ativa quando um plano é informado
                $this->createActiveSubscription($tenant, $planId);
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
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'due_day' => 1,
                'auto_renew' => true,
                'payment_method' => 'PIX',
            ]);

            $this->officialWhatsApp->sendByKey(
                'subscription.created',
                $tenant->phone,
                [
                    'customer_name' => $tenant->trade_name,
                    'tenant_name' => $tenant->trade_name,
                    'plan_name' => $plan->name,
                    'plan_amount' => 'R$ ' . number_format($plan->price_cents / 100, 2, ',', '.'),
                    'due_date' => $endsAt->format('d/m/Y'),
                ],
                [
                    'service' => static::class,
                    'tenant_id' => (string) $tenant->id,
                    'event' => 'subscription.created',
                ]
            );

            $planService = new TenantPlanService();
            $planService->applyPlanRules($tenant, $plan);

            Log::info("Assinatura ativa criada para tenant {$tenant->id} no plano {$plan->name}", [
                'test_plan' => $plan->isTest(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar assinatura automática: ' . $e->getMessage());
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

        $this->officialWhatsApp->sendByKey(
            'tenant.welcome',
            $tenant->phone,
            [
                'customer_name' => $tenant->trade_name,
                'tenant_name' => $tenant->trade_name,
                'login_url' => $loginUrl,
            ],
            [
                'service' => static::class,
                'tenant_id' => (string) $tenant->id,
                'event' => 'tenant.welcome',
            ]
        );
    }

    private function syncAsaas(Tenant $tenant): void
    {
        try {
            // A sincronização real de cliente é chamada pelo Controller se necessário.
        } catch (\Throwable $e) {
            Log::error("❌ Erro na sincronia Asaas via Service: " . $e->getMessage());
        }
    }
}

