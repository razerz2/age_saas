<?php

namespace App\Services\Platform;

use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\TenantAdmin;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Models\Platform\PlanAccessRule;
use App\Models\Tenant\TenantPlanLimit;
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
    public function processPaid(PreTenant $preTenant, array $webhookPayload = []): void
    {
        // 🔒 Verificação de idempotência: verifica se já foi processado
        $tenantCreatedLog = $preTenant->logs()->where('event', 'tenant_created')->first();
        if ($tenantCreatedLog) {
            $payload = is_string($tenantCreatedLog->payload) 
                ? json_decode($tenantCreatedLog->payload, true) 
                : $tenantCreatedLog->payload;
            $tenantId = $payload['tenant_id'] ?? null;
            
            if ($tenantId) {
                // Verifica se o tenant realmente existe
                $existingTenant = Tenant::find($tenantId);
                if ($existingTenant) {
                    Log::info("✅ Pré-tenant {$preTenant->id} já processado - tenant {$tenantId} já existe. Pulando criação.", [
                        'pre_tenant_id' => $preTenant->id,
                        'tenant_id' => $tenantId,
                    ]);
                    
                    // Verifica se precisa criar assinatura (caso tenha falhado antes)
                    $subscription = $existingTenant->subscriptions()->latest()->first();
                    if (!$subscription) {
                        Log::warning("⚠️ Tenant {$tenantId} existe mas não tem assinatura. Criando assinatura...", [
                            'pre_tenant_id' => $preTenant->id,
                            'tenant_id' => $tenantId,
                        ]);
                        $this->createSubscription($preTenant, $existingTenant, $webhookPayload);
                    }
                    
                    return; // Já foi processado, não precisa continuar
                }
            }
        }
        
        // Verifica se já está pago
        if ($preTenant->isPaid()) {
            Log::info("Pré-tenant {$preTenant->id} já está marcado como pago, mas tenant não foi encontrado. Tentando criar...");
        }

        // Marca como pago (idempotente)
        $preTenant->markAsPaid();

        try {
            // Garantir que usa conexão da plataforma
            DB::connection()->table('pre_tenant_logs')->insert([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'payment_confirmed',
                'payload' => json_encode(['message' => 'Pagamento confirmado via webhook']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Erro ao criar log de pagamento confirmado', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Criar tenant automaticamente
        $tenant = null;
        try {
            $tenant = $this->createTenantFromPreTenant($preTenant);
        } catch (\Throwable $e) {
            Log::error("Erro ao criar tenant a partir do pré-cadastro", [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Se o tenant foi criado no banco mas houve erro nas migrations, ainda podemos criar a assinatura
            // Verifica se o tenant existe mesmo com erro
            $tenantId = null;
            try {
                $tenantLog = DB::connection()->table('pre_tenant_logs')
                    ->where('pre_tenant_id', $preTenant->id)
                    ->where('event', 'tenant_created')
                    ->latest()
                    ->first();
                
                if ($tenantLog && !empty($tenantLog->payload)) {
                    $payload = is_string($tenantLog->payload) ? json_decode($tenantLog->payload, true) : $tenantLog->payload;
                    $tenantId = $payload['tenant_id'] ?? null;
                }
            } catch (\Throwable $logError) {
                // Ignora erro ao buscar log
            }
            
            // Se encontrou tenant_id, tenta buscar o tenant
            if ($tenantId) {
                try {
                    $tenant = Tenant::find($tenantId);
                    if ($tenant) {
                        Log::info("Tenant encontrado mesmo após erro na criação: {$tenant->id}");
                    }
                } catch (\Throwable $tenantError) {
                    // Ignora
                }
            }
            
            // Se não encontrou tenant, não pode continuar
            if (!$tenant) {
                // Registra erro mas não lança exceção para não quebrar o webhook
                try {
                    DB::connection()->table('pre_tenant_logs')->insert([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'processing_error',
                        'payload' => json_encode([
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $logError) {
                    Log::warning('Erro ao criar log de erro do processamento', [
                        'error' => $logError->getMessage(),
                    ]);
                }
                return; // Não pode continuar sem tenant
            }
        }

        // Se chegou aqui, temos um tenant (criado com sucesso ou recuperado após erro)
        if ($tenant) {
            try {
                // Criar assinatura
                Log::info("🔹 Criando assinatura para tenant {$tenant->id}", [
                    'pre_tenant_id' => $preTenant->id,
                ]);
                $this->createSubscription($preTenant, $tenant, $webhookPayload);

                // Enviar email com credenciais
                $this->sendWelcomeEmail($tenant, $preTenant);
            } catch (\Throwable $subscriptionError) {
                Log::error("Erro ao criar assinatura ou enviar email", [
                    'pre_tenant_id' => $preTenant->id,
                    'tenant_id' => $tenant->id,
                    'error' => $subscriptionError->getMessage(),
                    'trace' => $subscriptionError->getTraceAsString(),
                ]);
                
                // Registra erro mas não lança exceção para não quebrar o webhook
                try {
                    DB::connection()->table('pre_tenant_logs')->insert([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'subscription_creation_error',
                        'payload' => json_encode([
                            'error' => $subscriptionError->getMessage(),
                            'tenant_id' => $tenant->id,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $logError) {
                    Log::warning('Erro ao criar log de erro da assinatura', [
                        'error' => $logError->getMessage(),
                    ]);
                }
            }
        } else {
            Log::error("Falha ao criar tenant para pré-tenant {$preTenant->id} - método retornou null");
        }
    }

    /**
     * Cria tenant a partir do pré-cadastro
     */
    public function createTenantFromPreTenant(PreTenant $preTenant): ?Tenant
    {
        // 🔒 Verificação de idempotência: verifica se já existe tenant para este pré-cadastro
        $tenantCreatedLog = $preTenant->logs()->where('event', 'tenant_created')->first();
        if ($tenantCreatedLog) {
            $payload = is_string($tenantCreatedLog->payload) 
                ? json_decode($tenantCreatedLog->payload, true) 
                : $tenantCreatedLog->payload;
            $tenantId = $payload['tenant_id'] ?? null;
            
            if ($tenantId) {
                $existingTenant = Tenant::find($tenantId);
                if ($existingTenant) {
                    Log::info("✅ Tenant já existe para pré-tenant {$preTenant->id}. Retornando tenant existente.", [
                        'pre_tenant_id' => $preTenant->id,
                        'tenant_id' => $tenantId,
                    ]);
                    return $existingTenant;
                }
            }
        }
        
        // 🔒 Verificação adicional: verifica se já existe tenant com mesmo email ou asaas_customer_id
        if ($preTenant->asaas_customer_id) {
            $existingTenantByCustomer = Tenant::where('asaas_customer_id', $preTenant->asaas_customer_id)->first();
            if ($existingTenantByCustomer) {
                Log::info("✅ Tenant já existe com mesmo asaas_customer_id. Retornando tenant existente.", [
                    'pre_tenant_id' => $preTenant->id,
                    'tenant_id' => $existingTenantByCustomer->id,
                    'asaas_customer_id' => $preTenant->asaas_customer_id,
                ]);
                
                // Atualiza log do pré-tenant com o tenant encontrado
                try {
                    DB::connection()->table('pre_tenant_logs')->insert([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'tenant_found_existing',
                        'payload' => json_encode([
                            'tenant_id' => $existingTenantByCustomer->id,
                            'reason' => 'Tenant já existe com mesmo asaas_customer_id',
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Erro ao criar log de tenant encontrado', ['error' => $e->getMessage()]);
                }
                
                return $existingTenantByCustomer;
            }
        }
        
        DB::beginTransaction();

        try {
            // Validar subdomain
            $subdomain = $preTenant->subdomain_suggested;
            if (!$subdomain) {
                $subdomain = Str::slug($preTenant->fantasy_name ?? $preTenant->name);
            }
            $subdomain = Str::slug($subdomain);

            // Verificar se já existe (e gerar novo se necessário)
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

            // 🔒 Criar banco e rodar migrations (com verificação de idempotência)
            // O TenantProvisioner já verifica se o banco existe antes de criar
            $adminPassword = TenantProvisioner::createDatabase($tenant);

            // Gerar informações do admin
            $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
            $adminEmail = "admin@{$sanitizedSubdomain}.com";
            $loginUrl = url("/customer/{$tenant->subdomain}/login");

            // 💾 Salvar informações do admin na tabela tenant_admins
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
            
            Log::info("💾 Informações do admin salvas na tabela tenant_admins (PreTenant)", [
                'tenant_id' => $tenant->id,
                'pre_tenant_id' => $preTenant->id,
                'admin_email' => $adminEmail,
                'admin_password_saved' => !empty($adminPassword),
                'admin_password_length' => strlen($adminPassword),
                'admin_login_url' => $loginUrl,
            ]);

            // Garantir que usa conexão da plataforma (não do tenant)
            try {
                DB::connection()->table('pre_tenant_logs')->insert([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'tenant_created',
                    'payload' => json_encode([
                        'tenant_id' => $tenant->id,
                        'subdomain' => $subdomain,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de tenant criado', [
                    'error' => $logError->getMessage(),
                ]);
            }

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

            // Garantir que usa conexão da plataforma (não do tenant)
            try {
                DB::connection()->table('pre_tenant_logs')->insert([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'tenant_creation_error',
                    'payload' => json_encode(['error' => $e->getMessage()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de erro do tenant', [
                    'error' => $logError->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Cria assinatura para o tenant
     */
    public function createSubscription(PreTenant $preTenant, Tenant $tenant, array $webhookPayload = []): void
    {
        try {
            Log::info("🔹 Iniciando criação de assinatura", [
                'pre_tenant_id' => $preTenant->id,
                'tenant_id' => $tenant->id,
                'has_webhook_payload' => !empty($webhookPayload),
                'pre_tenant_plan_id' => $preTenant->plan_id, // 🔍 DEBUG: Verificar plan_id salvo
            ]);

            // 🔍 DEBUG: Verificar se plan_id está definido
            if (empty($preTenant->plan_id)) {
                Log::error("❌ ERRO CRÍTICO: plan_id está NULL no pré-tenant {$preTenant->id}", [
                    'pre_tenant_id' => $preTenant->id,
                    'pre_tenant_data' => $preTenant->toArray(),
                ]);
                return;
            }

            $plan = $preTenant->plan;
            if (!$plan) {
                Log::error("❌ Plano não encontrado para pré-tenant {$preTenant->id}", [
                    'pre_tenant_id' => $preTenant->id,
                    'plan_id_salvo' => $preTenant->plan_id,
                    'plan_exists' => \App\Models\Platform\Plan::where('id', $preTenant->plan_id)->exists(),
                ]);
                return;
            }

            Log::info("✅ Plano encontrado", [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_price_cents' => $plan->price_cents,
            ]);

            // Extrair dados do webhook
            $paymentData = $webhookPayload['payment'] ?? [];
            
            // Extrair data de pagamento (prioridade: confirmedDate > paymentDate > dueDate > now)
            $paymentDate = null;
            if (!empty($paymentData['confirmedDate'])) {
                $paymentDate = \Carbon\Carbon::parse($paymentData['confirmedDate']);
            } elseif (!empty($paymentData['paymentDate'])) {
                $paymentDate = \Carbon\Carbon::parse($paymentData['paymentDate']);
            } elseif (!empty($paymentData['dueDate'])) {
                $paymentDate = \Carbon\Carbon::parse($paymentData['dueDate']);
            } else {
                $paymentDate = now();
                Log::warning("Data de pagamento não encontrada no webhook ao criar assinatura, usando data atual", [
                    'pre_tenant_id' => $preTenant->id,
                ]);
            }
            
            // Identificar método de pagamento usado
            $billingType = $paymentData['billingType'] ?? 'PIX';
            $paymentMethod = match($billingType) {
                'CREDIT_CARD' => 'CREDIT_CARD',
                'DEBIT_CARD' => 'CREDIT_CARD', // Trata débito como crédito para assinatura
                'BOLETO' => 'PIX', // Boleto será tratado como PIX para renovação
                default => 'PIX',
            };

            // Verificar se já existe assinatura para este tenant
            $existingSubscription = Subscription::where('tenant_id', $tenant->id)->latest()->first();
            if ($existingSubscription) {
                Log::warning("⚠️ Já existe assinatura para tenant {$tenant->id}", [
                    'existing_subscription_id' => $existingSubscription->id,
                ]);
                // Usa a assinatura existente e apenas sincroniza com Asaas
                $this->syncSubscriptionWithAsaas($existingSubscription, $paymentDate);
                return;
            }

            // Criar assinatura local
            Log::info("🔹 Criando assinatura local", [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate->toDateString(),
            ]);

            // Define o dia de vencimento (padrão: dia 1 do mês, consistente com o formulário manual)
            $dueDay = 1;
            
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $paymentDate,
                'ends_at' => $paymentDate->copy()->addMonths($plan->period_months ?? 1),
                'due_day' => $dueDay,
                'auto_renew' => true,
                'payment_method' => $paymentMethod,
            ]);

            Log::info("✅ Assinatura local criada com sucesso", [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
            ]);

            try {
                // Garantir que usa conexão da plataforma (não do tenant)
                DB::connection()->table('pre_tenant_logs')->insert([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'subscription_created',
                    'payload' => json_encode([
                        'subscription_id' => $subscription->id,
                        'plan_id' => $plan->id,
                        'payment_method' => $paymentMethod,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de assinatura criada', [
                    'error' => $logError->getMessage(),
                ]);
            }

            Log::info("🔹 Iniciando criação de assinatura recorrente no Asaas", [
                'subscription_id' => $subscription->id,
                'payment_method' => $paymentMethod,
            ]);

            // Aplicar regras de acesso ao tenant
            $this->applyAccessRulesToTenant($subscription);

            // Sincronizar com Asaas (seguindo o mesmo padrão do SubscriptionController)
            $this->syncSubscriptionWithAsaas($subscription, $paymentDate);

        } catch (\Throwable $e) {
            Log::error('Erro ao criar assinatura', [
                'pre_tenant_id' => $preTenant->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Sincroniza assinatura com Asaas (seguindo o mesmo padrão do SubscriptionController)
     */
    public function syncSubscriptionWithAsaas(Subscription $subscription, \Carbon\Carbon $paymentDate): void
    {
        try {
            $asaas = new AsaasService();
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            if (!$tenant || !$plan) {
                Log::warning("Tenant ou plano não encontrado para assinatura {$subscription->id}");
                return;
            }

            // 🔹 Status inicial — aguardando sincronização
            $subscription->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'pending',
                'asaas_last_error' => null,
                'asaas_last_sync_at' => now(),
            ]);

            // 🔹 1. Garantir cliente vinculado no Asaas
            if (!$tenant->asaas_customer_id) {
                $search = $asaas->searchCustomer($tenant->email);

                if (!empty($search['data'][0]['id'])) {
                    $tenant->update(['asaas_customer_id' => $search['data'][0]['id']]);
                } else {
                    $customerResponse = $asaas->createCustomer($tenant);
                    if (empty($customerResponse) || !isset($customerResponse['id'])) {
                        $subscription->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'pending',
                            'asaas_last_error' => 'Falha ao criar cliente no Asaas (resposta vazia ou inválida).',
                            'asaas_last_sync_at' => now(),
                        ]);
                        Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar cliente no Asaas.");
                        return;
                    }
                    $tenant->update(['asaas_customer_id' => $customerResponse['id']]);
                }
            }

            // Calcular próxima data de vencimento baseada na data de pagamento
            $nextDueDate = $paymentDate->copy()->addMonths($plan->period_months ?? 1);

            // 🔹 2. Pagamento com CARTÃO + auto-renovação
            if ($subscription->payment_method === 'CREDIT_CARD' && $subscription->auto_renew) {
                // Cria nova assinatura no Asaas
                $response = $asaas->createSubscription([
                    'customer' => $tenant->asaas_customer_id,
                    'value' => $plan->price_cents / 100,
                    'cycle' => 'MONTHLY',
                    'nextDueDate' => $nextDueDate->toDateString(),
                    'description' => "Assinatura do plano {$plan->name}",
                ]);

                // Verificar se houve erro na resposta
                if (!empty($response['error'])) {
                    $errorMessage = $response['message'] ?? 'Falha ao criar assinatura recorrente no Asaas';
                    Log::error("❌ Falha ao criar assinatura recorrente no Asaas", [
                        'subscription_id' => $subscription->id,
                        'error' => $errorMessage,
                        'response' => $response,
                    ]);

                    $subscription->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_error' => $errorMessage,
                        'asaas_last_sync_at' => now(),
                    ]);
                    return;
                }

                if (empty($response) || !isset($response['subscription']['id'])) {
                    $subscription->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'pending',
                        'asaas_last_error' => 'Falha ao criar assinatura no Asaas (resposta vazia ou inválida).',
                        'asaas_last_sync_at' => now(),
                    ]);
                    Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar assinatura no Asaas.");
                    return;
                }

                // Sucesso — registra ID e fatura local
                $subscription->update(['asaas_subscription_id' => $response['subscription']['id']]);

                if (!empty($response['payment_link'])) {
                    Invoices::create([
                        'subscription_id' => $subscription->id,
                        'tenant_id' => $tenant->id,
                        'amount_cents' => $plan->price_cents,
                        'due_date' => $response['payment']['dueDate'] ?? $nextDueDate,
                        'status' => 'pending',
                        'payment_link' => $response['payment_link'],
                        'payment_method' => 'CREDIT_CARD',
                        'provider' => 'asaas',
                        'provider_id' => $response['subscription']['id'],
                        'asaas_payment_id' => $response['payment']['id'] ?? null,
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                    ]);
                }

                $subscription->update([
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_error' => null,
                    'asaas_last_sync_at' => now(),
                    'status' => 'active', // Já foi pago, então está ativo
                ]);
            }

            // 🔹 3. Pagamento PIX + auto-renovação
            elseif ($subscription->payment_method === 'PIX' && $subscription->auto_renew) {
                // Cria a primeira fatura para a próxima renovação (controle no sistema)
                $response = $asaas->createPayment([
                    'customer' => $tenant->asaas_customer_id,
                    'billingType' => 'PIX',
                    'dueDate' => $nextDueDate->toDateString(),
                    'value' => $plan->price_cents / 100,
                    'description' => "Assinatura do plano {$plan->name}",
                    'externalReference' => $subscription->id,
                ]);

                if (empty($response) || !isset($response['id'])) {
                    $subscription->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'pending',
                        'asaas_last_error' => 'Falha ao criar fatura PIX no Asaas (resposta vazia ou inválida).',
                        'asaas_last_sync_at' => now(),
                    ]);
                    Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar fatura PIX.");
                    return;
                }

                Invoices::create([
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $tenant->id,
                    'amount_cents' => $plan->price_cents,
                    'due_date' => $nextDueDate,
                    'status' => 'pending',
                    'payment_link' => $response['invoiceUrl'] ?? null,
                    'payment_method' => 'PIX',
                    'provider' => 'asaas',
                    'provider_id' => $response['id'],
                    'asaas_payment_id' => $response['id'],
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                ]);

                $subscription->update([
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => null,
                    'status' => 'active', // Já foi pago, então está ativo
                ]);
            }

            // 🔹 4. Trial / sem integração
            else {
                $subscription->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'skipped',
                    'asaas_last_error' => null,
                    'asaas_last_sync_at' => now(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Erro ao criar assinatura recorrente no Asaas', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $subscription->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_error' => $e->getMessage(),
                'asaas_last_sync_at' => now(),
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

            // Garantir que usa conexão da plataforma (não do tenant)
            try {
                DB::connection()->table('pre_tenant_logs')->insert([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'welcome_email_sent',
                    'payload' => json_encode(['email' => $preTenant->email]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                Log::warning('Erro ao criar log de email enviado', [
                    'error' => $logError->getMessage(),
                ]);
            }

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

    /**
     * Aplica regras de acesso do plano ao tenant
     */
    private function applyAccessRulesToTenant(Subscription $subscription)
    {
        try {
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            if (!$tenant || !$plan) {
                Log::warning("⚠️ Não foi possível aplicar regras: tenant ou plano não encontrado");
                return;
            }

            // Busca regra de acesso do plano
            $rule = PlanAccessRule::where('plan_id', $plan->id)
                ->with('features')
                ->first();

            if (!$rule) {
                Log::warning("⚠️ Regra de acesso não encontrada para o plano: {$plan->name}");
                return;
            }

            // Prepara dados para salvar no tenant
            $allowedFeatures = $rule->features->where('pivot.allowed', true)->pluck('name')->toArray();

            $limitsData = [
                'max_admin_users' => $rule->max_admin_users,
                'max_common_users' => $rule->max_common_users,
                'max_doctors' => $rule->max_doctors,
                'allowed_features' => $allowedFeatures,
            ];

            // Configura conexão do tenant
            config([
                'database.connections.tenant.host' => $tenant->db_host,
                'database.connections.tenant.port' => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            // Testa conexão
            try {
                DB::connection('tenant')->getPdo();
            } catch (\Throwable $e) {
                Log::error("❌ Erro ao conectar ao banco do tenant: {$e->getMessage()}");
                return;
            }

            // Salva ou atualiza limites no tenant (sempre terá apenas um registro)
            // Deleta registros existentes e cria novo
            TenantPlanLimit::query()->delete();
            TenantPlanLimit::create($limitsData);

            Log::info("✅ Regras de acesso aplicadas ao tenant: {$tenant->trade_name}", [
                'limits' => $limitsData,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao aplicar regras de acesso: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

