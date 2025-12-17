<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlanChangeRequest;
use App\Models\Platform\Subscription;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanChangeRequestController extends Controller
{
    /**
     * Exibe o formulário de solicitação de mudança de plano
     */
    public function create()
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            abort(404, 'Tenant não encontrado');
        }

        // Buscar assinatura atual
        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('error', 'Nenhuma assinatura encontrada.');
        }

        // Verificar se já existe solicitação pendente
        $pendingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('info', 'Você já possui uma solicitação de mudança de plano pendente.');
        }

        // Buscar todos os planos ativos (exceto o atual)
        $plans = Plan::where('is_active', true)
            ->where('id', '!=', $subscription->plan_id)
            ->orderBy('price_cents', 'asc')
            ->get();

        return view('tenant.plan-change-request.create', compact('subscription', 'plans'));
    }

    /**
     * Processa a solicitação de mudança de plano
     */
    public function store(Request $request)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            abort(404, 'Tenant não encontrado');
        }

        $validated = $request->validate([
            'requested_plan_id' => 'required|uuid|exists:plans,id',
            'requested_payment_method' => 'required|in:PIX,BOLETO,CREDIT_CARD,DEBIT_CARD',
            'reason' => 'nullable|string|max:1000',
        ], [
            'requested_plan_id.required' => 'Selecione um plano.',
            'requested_plan_id.exists' => 'O plano selecionado não existe.',
            'requested_payment_method.required' => 'Selecione uma forma de pagamento.',
            'requested_payment_method.in' => 'Forma de pagamento inválida.',
            'reason.max' => 'O motivo não pode ter mais de 1000 caracteres.',
        ]);

        // Buscar assinatura atual
        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            return back()->withErrors(['error' => 'Nenhuma assinatura encontrada.']);
        }

        // Verificar se já existe solicitação pendente
        $pendingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return back()->withErrors(['error' => 'Você já possui uma solicitação pendente.']);
        }

        // Verificar se o plano solicitado é diferente do atual
        if ($subscription->plan_id === $validated['requested_plan_id']) {
            return back()->withErrors(['error' => 'O plano solicitado é o mesmo do atual.']);
        }

        // Criar solicitação
        try {
            PlanChangeRequest::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'current_plan_id' => $subscription->plan_id,
                'requested_plan_id' => $validated['requested_plan_id'],
                'requested_payment_method' => $validated['requested_payment_method'],
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            Log::info('Solicitação de mudança de plano criada', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'requested_plan_id' => $validated['requested_plan_id'],
            ]);

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('success', 'Solicitação de mudança de plano enviada com sucesso! Aguarde a aprovação do administrador.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar solicitação de mudança de plano', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);

            return back()->withErrors(['error' => 'Erro ao processar solicitação. Tente novamente.']);
        }
    }
}
