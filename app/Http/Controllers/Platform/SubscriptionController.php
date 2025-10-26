<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;
use App\Http\Requests\SubscriptionRequest;
use Illuminate\Support\Facades\Log;
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

    public function store(SubscriptionRequest $request)
    {
        $data = $request->validated();
        $data['auto_renew'] = $request->has('auto_renew');

        $plan = Plan::findOrFail($data['plan_id']);
        $data['ends_at'] = Carbon::parse($data['starts_at'])->addMonths($plan->period_months);

        $subscription = Subscription::create($data);

        try {
            // 🔹 Cria assinatura no Asaas apenas se for cartão + renovação automática
            if ($subscription->payment_method === 'CREDIT_CARD' && $subscription->auto_renew) {
                $asaas = new AsaasService();
                $tenant = $subscription->tenant;

                // 🧠 Etapa 1: Garante cliente no Asaas
                if (!$tenant->asaas_customer_id) {

                    // 🔍 Primeiro tenta buscar por e-mail
                    $searchResponse = $asaas->searchCustomer($tenant->email);

                    if (isset($searchResponse['data']) && count($searchResponse['data']) > 0) {
                        // ✅ Cliente já existe no Asaas
                        $existingCustomer = $searchResponse['data'][0];
                        $tenant->update(['asaas_customer_id' => $existingCustomer['id']]);
                        Log::info("👤 Cliente Asaas já existente encontrado: {$existingCustomer['id']}");
                    } else {
                        // 🚀 Cria novo cliente no Asaas
                        $customerResponse = $asaas->createCustomer($tenant);
                        if (!isset($customerResponse['id'])) {
                            throw new \Exception('Falha ao criar cliente no Asaas.');
                        }
                        $tenant->update(['asaas_customer_id' => $customerResponse['id']]);
                        Log::info("✅ Novo cliente Asaas criado: {$customerResponse['id']}");
                    }
                }

                // 🧠 Etapa 2: Cria assinatura no Asaas
                $asaasResponse = $asaas->createSubscription([
                    'customer' => $tenant->asaas_customer_id,
                    'value' => $plan->price_cents / 100,
                    'cycle' => 'MONTHLY',
                    'nextDueDate' => $subscription->starts_at->toDateString(),
                    'description' => "Assinatura do plano {$plan->name}",
                ]);

                if (isset($asaasResponse['id'])) {
                    $subscription->update([
                        'asaas_subscription_id' => $asaasResponse['id'],
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                    ]);
                    Log::info("✅ Assinatura criada com sucesso no Asaas: {$asaasResponse['id']}");
                } else {
                    throw new \Exception('Erro ao criar assinatura no Asaas: ' . json_encode($asaasResponse));
                }
            }
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao sincronizar assinatura Asaas: {$e->getMessage()}");
            $subscription->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('Platform.subscriptions.index')
            ->with('success', 'Assinatura criada com sucesso!');
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
        $data = $request->validated();
        $data['auto_renew'] = $request->has('auto_renew');
        $subscription->update($data);

        try {
            $asaas = new AsaasService();
            $tenant = $subscription->tenant;

            // 🔹 Só sincroniza com Asaas se for cartão + renovação automática
            if ($subscription->payment_method === 'CREDIT_CARD' && $subscription->auto_renew) {

                // Se ainda não tiver cliente vinculado, tenta encontrar ou criar
                if (!$tenant->asaas_customer_id) {
                    $searchResponse = $asaas->searchCustomer($tenant->email);
                    if (isset($searchResponse['data']) && count($searchResponse['data']) > 0) {
                        $existing = $searchResponse['data'][0];
                        $tenant->update(['asaas_customer_id' => $existing['id']]);
                    } else {
                        $newCustomer = $asaas->createCustomer($tenant);
                        if (!isset($newCustomer['id'])) {
                            throw new \Exception('Falha ao criar cliente no Asaas.');
                        }
                        $tenant->update(['asaas_customer_id' => $newCustomer['id']]);
                    }
                }

                // Se ainda não tiver assinatura no Asaas → cria
                if (!$subscription->asaas_subscription_id) {
                    $response = $asaas->createSubscription([
                        'customer' => $tenant->asaas_customer_id,
                        'value' => $subscription->plan->price_cents / 100,
                        'cycle' => 'MONTHLY',
                        'nextDueDate' => $subscription->starts_at->toDateString(),
                        'description' => "Assinatura do plano {$subscription->plan->name}",
                    ]);

                    if (isset($response['id'])) {
                        $subscription->update([
                            'asaas_subscription_id' => $response['id'],
                            'asaas_synced' => true,
                            'asaas_sync_status' => 'success',
                            'asaas_last_sync_at' => now(),
                        ]);
                    } else {
                        throw new \Exception('Erro ao criar assinatura no Asaas: ' . json_encode($response));
                    }
                } else {
                    // Se já existe no Asaas → atualiza
                    $asaas->updateSubscription($subscription->asaas_subscription_id, [
                        'value' => $subscription->plan->price_cents / 100,
                        'description' => "Assinatura atualizada ({$subscription->plan->name})",
                    ]);

                    $subscription->update([
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                    ]);
                }
            } else {
                // Caso assinatura tenha deixado de ser cartão + auto-renovação
                // (ex: trocou pra PIX ou desativou auto_renew)
                // 🔸 Cancelamos no Asaas se ela existir lá
                if ($subscription->asaas_subscription_id) {
                    $asaas->deleteSubscription($subscription->asaas_subscription_id);
                    $subscription->update([
                        'asaas_subscription_id' => null,
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'canceled',
                        'asaas_last_sync_at' => now(),
                    ]);
                    Log::info("🗑️ Assinatura {$subscription->id} removida no Asaas (mudança de método/renovação).");
                }
            }
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao atualizar assinatura Asaas: {$e->getMessage()}");
            $subscription->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_error' => $e->getMessage(),
                'asaas_last_sync_at' => now(),
            ]);
        }

        return redirect()->route('Platform.subscriptions.index')
            ->with('success', 'Assinatura atualizada com sucesso!');
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
}
