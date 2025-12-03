<?php

namespace App\Services\Platform;

use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\Subscription;
use App\Services\TenantProvisioner;
use App\Services\SystemSettingsService;
use App\Services\AsaasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\TenantAdminCredentialsMail;

class PreTenantProcessorService
{
    /**
     * Processa um pré-tenant pago
     */
    public function processPaid(PreTenant $preTenant): void
    {
        if ($preTenant->isPaid()) {
            Log::warning("Pré-tenant {$preTenant->id} já está marcado como pago.");
            return;
        }

        $preTenant->markAsPaid();

        PreTenantLog::create([
            'pre_tenant_id' => $preTenant->id,
            'event' => 'payment_confirmed',
            'payload' => ['message' => 'Pagamento confirmado via webhook'],
        ]);

        // Criar tenant automaticamente
        $tenant = $this->createTenantFromPreTenant($preTenant);

        if ($tenant) {
            // Criar assinatura
            $this->createSubscription($preTenant, $tenant);

            // Enviar email com credenciais
            $this->sendWelcomeEmail($tenant, $preTenant);
        }
    }

    /**
     * Cria tenant a partir do pré-cadastro
     */
    public function createTenantFromPreTenant(PreTenant $preTenant): ?Tenant
    {
        DB::beginTransaction();

        try {
            // Validar subdomain
            $subdomain = $preTenant->subdomain_suggested;
            if (!$subdomain) {
                $subdomain = Str::slug($preTenant->fantasy_name ?? $preTenant->name);
            }
            $subdomain = Str::slug($subdomain);

            // Verificar se já existe
            if (Tenant::where('subdomain', $subdomain)->exists()) {
                $subdomain = $subdomain . '_' . Str::random(4);
            }

            // Gerar configuração do banco
            $dbConfig = TenantProvisioner::prepareDatabaseConfig(
                $preTenant->name,
                $preTenant->fantasy_name
            );

            // Criar tenant
            $tenant = Tenant::create([
                'legal_name' => $preTenant->name,
                'trade_name' => $preTenant->fantasy_name ?? $preTenant->name,
                'document' => $preTenant->document,
                'email' => $preTenant->email,
                'phone' => $preTenant->phone,
                'subdomain' => $subdomain,
                'status' => 'active',
                'asaas_customer_id' => $preTenant->asaas_customer_id,
                'asaas_synced' => true,
                'asaas_sync_status' => 'success',
                'asaas_last_sync_at' => now(),
                ...$dbConfig,
            ]);

            // Criar localização se houver
            if ($preTenant->address || $preTenant->country_id) {
                TenantLocalizacao::create([
                    'tenant_id' => $tenant->id,
                    'endereco' => $preTenant->address ?? '',
                    'cep' => $preTenant->zipcode,
                    'pais_id' => $preTenant->country_id,
                    'estado_id' => $preTenant->state_id,
                    'cidade_id' => $preTenant->city_id,
                ]);
            }

            DB::commit();

            // Criar banco e rodar migrations
            $adminPassword = TenantProvisioner::createDatabase($tenant);

            // Gerar informações do admin
            $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
            $adminEmail = "admin@{$sanitizedSubdomain}.com";
            $loginUrl = url("/t/{$tenant->subdomain}/login");

            // 💾 Salvar informações do admin no banco de dados usando DB direto para garantir persistência
            // Usar conexão pgsql (banco central) onde a tabela tenants está
            DB::connection('pgsql')
                ->table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'admin_login_url' => $loginUrl,
                    'admin_email' => $adminEmail,
                    'admin_password' => $adminPassword,
                    'updated_at' => now(),
                ]);
            
            // Recarregar do banco para confirmar que foi salvo
            $tenant->refresh();
            
            Log::info("💾 Informações do admin salvas no banco (PreTenant)", [
                'tenant_id' => $tenant->id,
                'pre_tenant_id' => $preTenant->id,
                'admin_email' => $tenant->admin_email,
                'admin_password_saved' => !empty($tenant->admin_password),
                'admin_password_length' => $tenant->admin_password ? strlen($tenant->admin_password) : 0,
                'admin_login_url' => $tenant->admin_login_url,
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'tenant_created',
                'payload' => [
                    'tenant_id' => $tenant->id,
                    'subdomain' => $subdomain,
                ],
            ]);

            // Buscar usuário admin criado
            $adminUser = $this->getAdminUser($tenant);

            // Salvar senha temporariamente para envio de email
            session()->flash('tenant_admin_password', $adminPassword);

            return $tenant;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erro ao criar tenant a partir do pré-cadastro', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'tenant_creation_error',
                'payload' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }

    /**
     * Cria assinatura para o tenant
     */
    public function createSubscription(PreTenant $preTenant, Tenant $tenant): void
    {
        try {
            $plan = $preTenant->plan;
            if (!$plan) {
                Log::warning("Plano não encontrado para pré-tenant {$preTenant->id}");
                return;
            }

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonths($plan->period_months ?? 1),
                'auto_renew' => true,
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'subscription_created',
                'payload' => [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            Log::info("Assinatura criada para tenant {$tenant->id}", [
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro ao criar assinatura', [
                'pre_tenant_id' => $preTenant->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia email de boas-vindas com credenciais
     */
    public function sendWelcomeEmail(Tenant $tenant, PreTenant $preTenant): void
    {
        try {
            $systemSettingsService = new SystemSettingsService();
            if (!$systemSettingsService->emailIsConfigured()) {
                Log::info("Email não enviado: SMTP não configurado para tenant {$tenant->id}");
                return;
            }

            // 💾 Buscar informações do admin do banco de dados (ou da sessão como fallback)
            $adminPassword = $tenant->admin_password ?? session('tenant_admin_password');
            $adminEmail = $tenant->admin_email;
            $loginUrl = $tenant->admin_login_url ?? url("/t/{$tenant->subdomain}/login");

            if (!$adminPassword) {
                Log::warning("Não foi possível enviar email: senha do admin não encontrada");
                return;
            }

            // Se não tiver email salvo, gerar dinamicamente
            if (!$adminEmail) {
                $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
                $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
                $adminEmail = "admin@{$sanitizedSubdomain}.com";
            }

            Mail::to($preTenant->email)->send(
                new TenantAdminCredentialsMail(
                    $tenant,
                    $loginUrl,
                    $adminEmail,
                    $adminPassword
                )
            );

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'welcome_email_sent',
                'payload' => ['email' => $preTenant->email],
            ]);

            Log::info("Email de boas-vindas enviado para {$preTenant->email}");

        } catch (\Throwable $e) {
            Log::error("Erro ao enviar email de boas-vindas", [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Busca usuário admin do tenant
     */
    private function getAdminUser(Tenant $tenant): ?\stdClass
    {
        try {
            config([
                'database.connections.tenant.host' => $tenant->db_host,
                'database.connections.tenant.port' => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
            $adminEmail = "admin@{$sanitizedSubdomain}.com";

            $adminUser = DB::connection('tenant')
                ->table('users')
                ->where('email', $adminEmail)
                ->orWhere('name', 'Administrador')
                ->first();

            return $adminUser;

        } catch (\Throwable $e) {
            Log::warning('Não foi possível buscar usuário admin', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

