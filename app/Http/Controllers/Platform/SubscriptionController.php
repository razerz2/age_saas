<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'starts_at' => 'required|date',
            'due_day' => 'required|integer|min:1|max:28',
            'status' => 'required|in:active,past_due,canceled,trialing',
            'auto_renew' => 'boolean',
        ]);

        // ⚠️ Verifica se o tenant já possui uma assinatura ativa ou trial
        $exists = Subscription::where('tenant_id', $data['tenant_id'])
            ->whereIn('status', ['active', 'trialing'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['general' => 'Este tenant já possui uma assinatura ativa ou em teste.'])
                ->withInput();
        }

        // Calcula data de término baseada no plano
        $plan = Plan::findOrFail($data['plan_id']);
        $data['ends_at'] = Carbon::parse($data['starts_at'])->addMonths($plan->period_months);
        $data['auto_renew'] = $request->has('auto_renew');

        Subscription::create($data);

        return redirect()->route('Platform.subscriptions.index')
            ->with('success', 'Assinatura criada com sucesso!');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);
        return view('platform.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $tenants = Tenant::orderBy('trade_name')->get();
        $plans = Plan::orderBy('name')->get();

        return view('platform.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'due_day' => 'required|integer|min:1|max:28',
            'status' => 'required|in:active,past_due,canceled,trialing',
            'auto_renew' => 'boolean',
        ]);

        $data['auto_renew'] = $request->has('auto_renew');

        $subscription->update($data);

        return redirect()->route('Platform.subscriptions.index')->with('success', 'Assinatura atualizada com sucesso!');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('Platform.subscriptions.index')->with('success', 'Assinatura excluída com sucesso!');
    }

    public function renew($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            // Verifica se já há fatura pendente ou vencida
            $hasInvoice = Invoices::where('subscription_id', $subscription->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if ($hasInvoice) {
                return back()->with('error', 'Já existe uma fatura pendente ou vencida para esta assinatura.');
            }

            $asaas = new AsaasService();
            $invoice = $asaas->createInvoiceForSubscription($subscription);

            if ($invoice) {
                return back()->with('success', 'Nova fatura gerada com sucesso!');
            }

            return back()->with('error', 'Falha ao gerar nova fatura. Verifique o log para mais detalhes.');
        } catch (\Exception $e) {
            Log::error("Erro ao renovar assinatura {$id}: {$e->getMessage()}");
            return back()->with('error', 'Erro inesperado ao tentar gerar nova fatura.');
        }
    }

    public function getByTenant($tenantId)
    {
        $subscriptions = Subscription::query()
            ->with(['plan:id,name,price_cents'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get(['id', 'plan_id', 'status'])
            ->map(fn($sub) => [
                'id'     => $sub->id,
                'name'   => $sub->plan?->name ?? 'Plano não identificado',
                'value'  => $sub->plan?->price_cents ? $sub->plan->price_cents / 100 : 0, // 💰 conversão para reais
                'status' => ucfirst($sub->status),
            ]);

        return response()->json($subscriptions);
    }
}
