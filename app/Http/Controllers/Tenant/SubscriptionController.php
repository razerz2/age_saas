<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Exibe os detalhes da assinatura atual da tenant
     */
    public function show()
    {
        // Verificar se o usuário é administrador
        $user = auth('tenant')->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem visualizar informações de assinatura.');
        }

        $tenant = Tenant::current();
        
        if (!$tenant) {
            abort(404, 'Tenant não encontrado');
        }

        // Buscar assinatura ativa ou mais recente
        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with(['plan', 'invoices' => function($query) {
                $query->orderBy('due_date', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            // Se não houver assinatura, retornar view com mensagem
            return view('tenant.subscription.show', [
                'subscription' => null,
                'invoices' => collect(),
                'pendingInvoices' => collect(),
                'planFeatures' => collect(),
            ]);
        }

        // Buscar todas as faturas da assinatura
        $invoices = Invoices::where('subscription_id', $subscription->id)
            ->orderBy('due_date', 'desc')
            ->get();

        // Buscar faturas em aberto (pending ou overdue)
        $pendingInvoices = $invoices->whereIn('status', ['pending', 'overdue']);

        // Buscar funcionalidades do plano (se houver)
        $planFeatures = [];
        if ($subscription->plan && $subscription->plan->features) {
            $planFeatures = is_array($subscription->plan->features) 
                ? $subscription->plan->features 
                : json_decode($subscription->plan->features, true) ?? [];
        }

        // Verificar se há solicitação pendente de mudança de plano
        $pendingPlanChangeRequest = \App\Models\Platform\PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->with('requestedPlan')
            ->first();

        return view('tenant.subscription.show', compact(
            'subscription',
            'invoices',
            'pendingInvoices',
            'planFeatures',
            'pendingPlanChangeRequest'
        ));
    }
}

