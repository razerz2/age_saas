<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Invoices;
use App\Models\Platform\PlanAccessRule;
use App\Models\Tenant\TenantPlanLimit;
use App\Services\AsaasService;
use App\Http\Requests\Platform\SubscriptionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $tenants = Tenant::orderBy('trade_name')->get();
        $plans = Plan::orderBy('name')->get();

        return view('platform.subscriptions.create', compact('tenants', 'plans'));
    }

    public function show(Subscription $subscription)
    {
        // 🔹 Carrega as relações (ex: tenant e plan, se existirem)
        $subscription->load(['tenant', 'plan']);

        // 🔹 Retorna a view com os dados
        return view('platform.subscriptions.show', compact('subscription'));
    }


    public function store(SubscriptionRequest $request)
    {
        try {
            $data = $request->validated();
            $data['auto_renew'] = $request->has('auto_renew');

            // 🔹 Define plano e período
            $plan = Plan::findOrFail($data['plan_id']);
            $data['starts_at'] = $data['starts_at'] ?? now();
            $data['ends_at'] = \Carbon\Carbon::parse($data['starts_at'])
                ->addDays($plan->period_months * 30);

            // 🔹 Define status inicial
            $data['status'] = ($data['status'] ?? null) === 'trialing'
                ? 'trialing'
                : 'pending';

            // 🔹 Cria assinatura local
            $subscription = Subscription::create($data);

            // 🔹 Aplica regras de acesso ao tenant
            $this->applyAccessRulesToTenant($subscription);

            // 🔹 Chama o método de sincronização (centralizado)
            $result = $this->syncWithAsaas($subscription);

            // 🔹 Se houve falha de sincronização, apenas avisa
            if ($result === false) {
                return redirect()
                    ->route('Platform.subscriptions.index')
                    ->with('warning', 'Assinatura criada, mas houve falha na sincronização com o Asaas. Verifique os logs.');
            }

            // 🔹 Caso contrário, sucesso
            return redirect()
                ->route('Platform.subscriptions.index')
                ->with('success', 'Assinatura criada com sucesso e sincronizada com o Asaas!');
        } catch (\Throwable $e) {
            Log::error("💥 Erro ao criar assinatura: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            // Marca assinatura como falha de sincronização, se já existir
            if (isset($subscription)) {
                $subscription->update([
                    'asaas_synced'       => false,
                    'asaas_sync_status'  => 'failed',
                    'asaas_last_error'   => $e->getMessage(),
                    'asaas_last_sync_at' => now(),
                ]);
            }

            return back()->withInput()->withErrors([
                'general' => 'Erro ao criar assinatura. Verifique os logs para mais detalhes.',
            ]);
        }
    }


    public function edit(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);

        $tenants = Tenant::orderBy('trade_name')->get();
        $plans = Plan::orderBy('name')->get();

        return view('platform.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }


    public function update(SubscriptionRequest $request, Subscription $subscription)
    {
        try {
            $data = $request->validated();
            $data['auto_renew'] = $request->has('auto_renew');

            // 🔹 Campos críticos que exigem ressincronização
            $camposCriticos = ['plan_id', 'payment_method', 'auto_renew', 'status'];
            $houveMudanca = false;

            foreach ($camposCriticos as $campo) {
                if (array_key_exists($campo, $data) && $subscription->{$campo} != $data[$campo]) {
                    $houveMudanca = true;
                    break;
                }
            }

            // 🔹 Atualiza assinatura local (NUNCA sobrescreve IDs de integração)
            unset(
                $data['asaas_subscription_id'],
                $data['asaas_synced'],
                $data['asaas_sync_status'],
                $data['asaas_last_error'],
                $data['asaas_last_sync_at']
            );

            $subscription->update($data);

            // 🔹 Se o plano mudou, aplica novas regras de acesso
            if (isset($data['plan_id']) && $subscription->plan_id != $data['plan_id']) {
                $this->applyAccessRulesToTenant($subscription->fresh());
            }

            // 🔹 Se houve mudança crítica → reenviar sincronismo ao Asaas
            if ($houveMudanca) {
                Log::info("🔁 Mudança crítica detectada na assinatura {$subscription->id}. Reenviando sincronismo...");
                $this->syncWithAsaas($subscription);
            }

            // 🔹 Atualiza invoice local se valor do plano mudou
            $plan = $subscription->plan;
            $invoice = $subscription->invoices()->latest()->first();

            if ($invoice && $plan && $invoice->amount_cents != $plan->price_cents) {
                $invoice->update([
                    'amount_cents'       => $plan->price_cents,
                    'asaas_sync_status'  => 'pending',
                    'asaas_last_sync_at' => now(),
                ]);

                Log::info("💰 Fatura {$invoice->id} atualizada com novo valor do plano {$plan->name} ({$plan->price_cents}).");
            }

            // 🔹 Caso assinatura já tenha ID do Asaas e ainda não esteja marcada como sincronizada
            if ($subscription->asaas_subscription_id && !$subscription->asaas_synced) {
                $subscription->update([
                    'asaas_synced'       => true,
                    'asaas_sync_status'  => 'success',
                    'asaas_last_sync_at' => now(),
                ]);
            }

            return redirect()
                ->route('Platform.subscriptions.index')
                ->with('success', 'Assinatura atualizada com sucesso.' . ($houveMudanca ? ' Sincronismo reenviado ao Asaas.' : ''));
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao atualizar assinatura: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Erro ao atualizar assinatura.']);
        }
    }




    public function destroy(Subscription $subscription)
    {
        try {
            $asaas = new AsaasService();

            if ($subscription->asaas_subscription_id) {
                $asaas->deleteSubscription($subscription->asaas_subscription_id);
                Log::info("🗑️ Assinatura {$subscription->asaas_subscription_id} cancelada no Asaas antes da exclusão local.");
            }

            $subscription->delete();

            return redirect()->route('Platform.subscriptions.index')
                ->with('success', 'Assinatura excluída com sucesso!');
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao excluir assinatura {$subscription->id}: {$e->getMessage()}");
            return back()->withErrors(['general' => 'Erro ao excluir assinatura.']);
        }
    }

    // Outras funções (show, renew, getByTenant) permanecem as mesmas...
    public function syncWithAsaas(Subscription $subscription)
    {
        try {
            $asaas = new AsaasService();
            $tenant = $subscription->tenant;
            $plan   = $subscription->plan;

            // 🔹 Status inicial — aguardando sincronização
            $subscription->update([
                'asaas_synced'       => false,
                'asaas_sync_status'  => 'pending',
                'asaas_last_error'   => null,
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
                            'asaas_synced'       => false,
                            'asaas_sync_status'  => 'pending',
                            'asaas_last_error'   => 'Falha ao criar cliente no Asaas (resposta vazia ou inválida).',
                            'asaas_last_sync_at' => now(),
                        ]);

                        Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar cliente no Asaas.");
                        return back()->withErrors(['general' => 'Não foi possível sincronizar com o Asaas no momento. Tente novamente.']);
                    }

                    $tenant->update(['asaas_customer_id' => $customerResponse['id']]);
                }
            }

            // 🔹 2. Pagamento com CARTÃO + auto-renovação
            if ($subscription->payment_method === 'CREDIT_CARD' && $subscription->auto_renew) {

                if ($subscription->asaas_subscription_id) {
                    // Atualiza assinatura existente
                    $asaas->updateSubscription($subscription->asaas_subscription_id, [
                        'value'       => $plan->price_cents / 100,
                        'description' => "Assinatura atualizada ({$plan->name})",
                    ]);
                    Log::info("🔄 Assinatura {$subscription->id} atualizada no Asaas.");
                } else {
                    // Cria nova assinatura
                    $response = $asaas->createSubscription([
                        'customer'    => $tenant->asaas_customer_id,
                        'value'       => $plan->price_cents / 100,
                        'cycle'       => 'MONTHLY',
                        'nextDueDate' => now()->toDateString(),
                        'description' => "Assinatura do plano {$plan->name}",
                    ]);

                    if (empty($response) || !isset($response['subscription']['id'])) {
                        $subscription->update([
                            'asaas_synced'       => false,
                            'asaas_sync_status'  => 'pending',
                            'asaas_last_error'   => 'Falha ao criar assinatura no Asaas (resposta vazia ou inválida).',
                            'asaas_last_sync_at' => now(),
                        ]);

                        Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar assinatura no Asaas.");
                        return back()->withErrors(['general' => 'Não foi possível criar a assinatura no Asaas.']);
                    }

                    // Sucesso — registra ID e fatura local
                    $subscription->update(['asaas_subscription_id' => $response['subscription']['id']]);

                    if (!empty($response['payment_link'])) {
                        Invoices::create([
                            'subscription_id'    => $subscription->id,
                            'tenant_id'          => $tenant->id,
                            'amount_cents'       => $plan->price_cents,
                            'due_date'           => $response['payment']['dueDate'] ?? now()->addDay(),
                            'status'             => 'pending',
                            'payment_link'       => $response['payment_link'],
                            'payment_method'     => 'CREDIT_CARD',
                            'provider'           => 'asaas',
                            'provider_id'        => $response['subscription']['id'],
                            'asaas_payment_id'   => $response['payment']['id'] ?? null,
                            'asaas_synced'       => true,
                            'asaas_sync_status'  => 'success',
                            'asaas_last_sync_at' => now(),
                        ]);
                    }
                }

                $subscription->update([
                    'asaas_synced'       => true,
                    'asaas_sync_status'  => 'success',
                    'asaas_last_error'   => null,
                    'asaas_last_sync_at' => now(),
                    'status'             => 'pending',
                ]);
            }

            // 🔹 3. Pagamento PIX + auto-renovação
            elseif ($subscription->payment_method === 'PIX' && $subscription->auto_renew) {
                $response = $asaas->createPayment([
                    'customer'          => $tenant->asaas_customer_id,
                    'billingType'       => 'PIX',
                    'dueDate'           => now()->addDays(5)->toDateString(),
                    'value'             => $plan->price_cents / 100,
                    'description'       => "Assinatura do plano {$plan->name}",
                    'externalReference' => $subscription->id,
                ]);

                if (empty($response) || !isset($response['id'])) {
                    $subscription->update([
                        'asaas_synced'       => false,
                        'asaas_sync_status'  => 'pending',
                        'asaas_last_error'   => 'Falha ao criar fatura PIX no Asaas (resposta vazia ou inválida).',
                        'asaas_last_sync_at' => now(),
                    ]);

                    Log::warning("⚠️ Subscription {$subscription->id}: resposta inválida ao criar fatura PIX.");
                    return back()->withErrors(['general' => 'Não foi possível gerar fatura PIX no Asaas.']);
                }

                Invoices::create([
                    'subscription_id'    => $subscription->id,
                    'tenant_id'          => $tenant->id,
                    'amount_cents'       => $plan->price_cents,
                    'due_date'           => now()->addDays(5),
                    'status'             => 'pending',
                    'payment_link'       => $response['invoiceUrl'] ?? null,
                    'payment_method'     => 'PIX',
                    'provider'           => 'asaas',
                    'provider_id'        => $response['id'],
                    'asaas_payment_id'   => $response['id'],
                    'asaas_synced'       => true,
                    'asaas_sync_status'  => 'success',
                    'asaas_last_sync_at' => now(),
                ]);

                $subscription->update([
                    'asaas_synced'       => true,
                    'asaas_sync_status'  => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error'   => null,
                    'status'             => 'pending',
                ]);
            }

            // 🔹 4. Cancelamento / método alterado
            elseif ($subscription->asaas_subscription_id) {
                $asaas->deleteSubscription($subscription->asaas_subscription_id);
                $subscription->update([
                    'asaas_subscription_id' => null,
                    'asaas_synced'          => true,
                    'asaas_sync_status'     => 'canceled',
                    'asaas_last_error'      => null,
                    'asaas_last_sync_at'    => now(),
                ]);
                Log::info("🗑️ Assinatura {$subscription->id} removida no Asaas (mudança de método ou auto_renew desativado).");
            }

            // 🔹 5. Trial / sem integração
            else {
                $subscription->update([
                    'asaas_synced'       => false,
                    'asaas_sync_status'  => 'skipped',
                    'asaas_last_error'   => null,
                    'asaas_last_sync_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Sincronização com Asaas concluída com sucesso!');
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao sincronizar assinatura com Asaas: {$e->getMessage()}");

            $subscription->update([
                'asaas_synced'       => false,
                'asaas_sync_status'  => 'failed',
                'asaas_last_error'   => $e->getMessage(),
                'asaas_last_sync_at' => now(),
            ]);

            return back()->withErrors(['general' => 'Erro ao sincronizar com Asaas.']);
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

