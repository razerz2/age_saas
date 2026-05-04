<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Invoices;
use App\Models\Platform\PlanChangeRequest;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Exibe os detalhes da assinatura atual da tenant
     */
    public function show()
    {
        // Verificar se o usuário é administrador
        $user = auth('tenant')->user();
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem visualizar informações de assinatura.');
        }

        $currentTenant = Tenant::current();

        if (! $currentTenant) {
            abort(404, 'Tenant não encontrado');
        }

        // Buscar assinatura ativa ou mais recente
        $subscription = Subscription::where('tenant_id', $currentTenant->id)
            ->with(['plan', 'invoices' => function ($query) {
                $query->orderBy('due_date', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->first();

        $invoices = collect();
        $openInvoices = collect();
        $primaryOpenInvoice = null;
        $latestPaidInvoice = null;
        $nextDueInvoice = null;
        $nextDueDate = null;
        $canPayNow = false;
        $paymentActionLabel = 'Ver pagamento';
        $paymentUnavailableMessage = null;
        $planFeatures = [];

        if ($subscription) {
            $invoices = Invoices::where('subscription_id', $subscription->id)
                ->orderBy('due_date', 'desc')
                ->get();

            // Faturas em aberto (pending/overdue)
            $openInvoices = $invoices->whereIn('status', ['pending', 'overdue'])->values();

            // Prioridade: overdue mais antiga, senão pending mais próxima
            $primaryOpenInvoice = $openInvoices
                ->where('status', 'overdue')
                ->sortBy('due_date')
                ->first();

            if (! $primaryOpenInvoice) {
                $primaryOpenInvoice = $openInvoices
                    ->where('status', 'pending')
                    ->sortBy('due_date')
                    ->first();
            }

            // Fatura paga mais recente: paid_at desc, fallback due_date desc
            $latestPaidInvoice = $invoices
                ->where('status', 'paid')
                ->sort(function ($a, $b) {
                    $aKey = $a->paid_at ?? $a->due_date;
                    $bKey = $b->paid_at ?? $b->due_date;
                    $aTs = $aKey ? Carbon::parse($aKey)->timestamp : 0;
                    $bTs = $bKey ? Carbon::parse($bKey)->timestamp : 0;

                    return $bTs <=> $aTs;
                })
                ->first();

            // Próxima fatura: principal aberta, senão pending futura, senão null
            if ($primaryOpenInvoice) {
                $nextDueInvoice = $primaryOpenInvoice;
            } else {
                $nextDueInvoice = $invoices
                    ->where('status', 'pending')
                    ->filter(function ($invoice) {
                        return $invoice->due_date && Carbon::parse($invoice->due_date)->isFuture();
                    })
                    ->sortBy('due_date')
                    ->first();
            }

            // Próxima data: due_date da próxima fatura, senão ends_at
            $nextDueDate = $nextDueInvoice?->due_date ?? $subscription->ends_at;

            if ($subscription->plan && $subscription->plan->features) {
                $planFeatures = is_array($subscription->plan->features)
                    ? $subscription->plan->features
                    : json_decode($subscription->plan->features, true) ?? [];
            }

            // Ação de pagamento segura
            $primaryPaymentLink = $primaryOpenInvoice->payment_link ?? null;
            $isPrimaryOpenStatus = in_array($primaryOpenInvoice->status ?? null, ['pending', 'overdue'], true);
            $hasValidPaymentLink = is_string($primaryPaymentLink)
                && filter_var($primaryPaymentLink, FILTER_VALIDATE_URL);

            $canPayNow = (bool) ($primaryOpenInvoice && $isPrimaryOpenStatus && $hasValidPaymentLink);

            if (($primaryOpenInvoice->status ?? null) === 'overdue') {
                $paymentActionLabel = 'Regularizar agora';
            } elseif (($primaryOpenInvoice->status ?? null) === 'pending') {
                $paymentActionLabel = 'Pagar agora';
            }

            if ($primaryOpenInvoice && ! $hasValidPaymentLink) {
                $paymentUnavailableMessage = 'Link de pagamento indisponível. Entre em contato com o suporte.';
            }
        }

        // Solicitação pendente de mudança de plano
        $pendingPlanChangeRequest = PlanChangeRequest::where('tenant_id', $currentTenant->id)
            ->where('status', 'pending')
            ->with('requestedPlan')
            ->first();

        // Status comercial consolidado
        $rawStatus = $subscription->status ?? null;
        $statusText = $rawStatus ? Str::headline(str_replace('_', ' ', $rawStatus)) : 'Sem assinatura';

        $commercialStatus = [
            'label' => $statusText,
            'variant' => 'secondary',
            'message' => null,
        ];

        if ($currentTenant->suspended_at) {
            $commercialStatus = [
                'label' => 'Suspenso',
                'variant' => 'danger',
                'message' => 'O tenant está suspenso no momento.',
            ];
        } elseif ($rawStatus === 'active') {
            $commercialStatus = ['label' => 'Ativo', 'variant' => 'success', 'message' => null];
        } elseif ($rawStatus === 'pending') {
            $commercialStatus = ['label' => 'Pendente', 'variant' => 'warning', 'message' => null];
        } elseif ($rawStatus === 'past_due') {
            $commercialStatus = ['label' => 'Em atraso', 'variant' => 'danger', 'message' => null];
        } elseif ($rawStatus === 'trialing') {
            $commercialStatus = ['label' => 'Trial', 'variant' => 'info', 'message' => null];
        } elseif ($rawStatus === 'canceled') {
            $commercialStatus = ['label' => 'Cancelado', 'variant' => 'secondary', 'message' => null];
        }

        return view('tenant.subscription.show', compact(
            'currentTenant',
            'subscription',
            'invoices',
            'openInvoices',
            'primaryOpenInvoice',
            'latestPaidInvoice',
            'nextDueInvoice',
            'nextDueDate',
            'commercialStatus',
            'canPayNow',
            'paymentActionLabel',
            'paymentUnavailableMessage',
            'planFeatures',
            'pendingPlanChangeRequest'
        ));
    }

    public function refreshInvoiceStatus(Request $request, Invoices $invoice)
    {
        $user = auth('tenant')->user();
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        $currentTenant = Tenant::current();
        if (! $currentTenant) {
            abort(404, 'Tenant não encontrado.');
        }

        $tenantSubscriptionIds = Subscription::where('tenant_id', $currentTenant->id)->pluck('id');
        $belongsToTenant = (string) $invoice->tenant_id === (string) $currentTenant->id;
        $belongsToTenantSubscription = $tenantSubscriptionIds->contains($invoice->subscription_id);

        if (! $belongsToTenant || ! $belongsToTenantSubscription) {
            abort(404);
        }

        Log::info('Tenant invoice refresh requested', [
            'tenant_id' => $currentTenant->id,
            'invoice_id' => $invoice->id,
            'user_id' => $user->id ?? null,
        ]);

        $paymentId = $invoice->asaas_payment_id ?: $invoice->provider_id;
        if (! $paymentId) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('warning', 'Não foi possível consultar esta fatura no provedor de pagamento.');
        }

        try {
            /** @var AsaasService $asaas */
            $asaas = app(AsaasService::class);
            $response = $asaas->getPaymentStatus((string) $paymentId);
            $asaasStatus = strtoupper((string) ($response['status'] ?? ''));

            if ($asaasStatus === '') {
                return redirect()
                    ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                    ->with('warning', 'Não foi possível consultar esta fatura no provedor de pagamento.');
            }

            $newStatus = match ($asaasStatus) {
                'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'paid',
                'OVERDUE' => 'overdue',
                'PENDING', 'AWAITING_RISK_ANALYSIS' => 'pending',
                default => $invoice->status,
            };

            $payload = ['status' => $newStatus];
            if ($newStatus === 'paid' && ! $invoice->paid_at) {
                $payload['paid_at'] = now();
            }

            $invoice->update($payload);

            if ($newStatus === 'paid' || $newStatus === 'overdue') {
                return redirect()
                    ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                    ->with('success', 'Status da fatura atualizado com sucesso.');
            }

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('info', 'A fatura ainda está aguardando pagamento.');
        } catch (\Throwable $e) {
            Log::error('Tenant invoice refresh failed', [
                'tenant_id' => $currentTenant->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('error', 'Erro ao atualizar status da fatura. Tente novamente.');
        }
    }

    public function requestCancellation(Request $request)
    {
        $user = auth('tenant')->user();
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem solicitar cancelamento.');
        }

        $currentTenant = Tenant::current();
        if (! $currentTenant) {
            abort(404, 'Tenant não encontrado.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $subscription = Subscription::where('tenant_id', $currentTenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $subscription) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('error', 'Assinatura não encontrada.');
        }

        if ($subscription->status === 'canceled') {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('info', 'O cancelamento desta assinatura já foi solicitado.');
        }

        $alreadyRequested = (bool) $subscription->cancel_at_period_end
            || ! is_null($subscription->cancel_requested_at)
            || $subscription->cancellation_status === 'pending_period_end';

        if ($alreadyRequested) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('info', 'O cancelamento desta assinatura já foi solicitado.');
        }

        try {
            $subscription->update([
                'cancel_requested_at' => now(),
                'cancel_at_period_end' => true,
                'cancellation_reason' => $validated['reason'] ?? null,
                'cancellation_requested_by' => isset($user->id) ? (string) $user->id : null,
                'cancellation_status' => 'pending_period_end',
                'auto_renew' => false,
            ]);

            Log::info('Tenant scheduled cancellation requested', [
                'tenant_id' => $currentTenant->id,
                'subscription_id' => $subscription->id,
                'user_id' => $user->id ?? null,
                'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
                'cancellation_status' => $subscription->cancellation_status,
            ]);

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('success', 'Cancelamento solicitado com sucesso. Sua assinatura permanecerá ativa até o fim do ciclo atual.');
        } catch (\Throwable $e) {
            Log::error('Tenant scheduled cancellation request failed', [
                'tenant_id' => $currentTenant->id,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $currentTenant->subdomain])
                ->with('error', 'Erro ao solicitar cancelamento. Tente novamente.');
        }
    }
}
