<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Models\Platform\PlanChangeRequest;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\Platform\PaymentMethodAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PlanChangeRequestController extends Controller
{
    public function create()
    {
        $tenant = Tenant::current();

        if (! $tenant) {
            abort(404, 'Tenant nao encontrado');
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $subscription) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('error', 'Nenhuma assinatura encontrada.');
        }

        $pendingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('info', 'Voce ja possui uma solicitacao de mudanca de plano pendente.');
        }

        $isTrialConversionContext = (bool) $subscription->is_trial
            || (! $tenant->isEligibleForAccess() && (bool) $tenant->expiredTrialSubscription());

        $plansQuery = Plan::where('is_active', true)
            ->where('id', '!=', $subscription->plan_id);

        if ($isTrialConversionContext) {
            $plansQuery->where('plan_type', Plan::TYPE_REAL);
        }

        $plans = $plansQuery
            ->orderBy('price_cents', 'asc')
            ->get();

        $paymentMethodService = app(PaymentMethodAvailabilityService::class);
        $enabledMethods = $paymentMethodService->enabledMethods();
        $optionsByMethod = collect($paymentMethodService->options())
            ->keyBy(fn (array $option): string => (string) ($option['method'] ?? ''));

        $paymentLabels = [
            'PIX' => 'Pix manual',
            'PIX_RECURRENT' => 'Pix recorrente',
            'BOLETO' => 'Boleto bancário',
            'CREDIT_CARD' => 'Cartão de crédito',
            'DEBIT_CARD' => 'Cartão de débito',
            'PIX_AUTOMATIC' => 'Pix Automático',
        ];

        $paymentMethodOptions = collect($enabledMethods)
            ->map(function (string $method) use ($optionsByMethod, $paymentLabels): array {
                $option = $optionsByMethod->get($method, []);

                return [
                    'value' => $method,
                    'label' => $paymentLabels[$method] ?? ($option['label'] ?? $method),
                ];
            })
            ->values()
            ->all();

        return view('tenant.plan-change-request.create', compact(
            'subscription',
            'plans',
            'isTrialConversionContext',
            'paymentMethodOptions'
        ));
    }

    public function store(Request $request)
    {
        $tenant = Tenant::current();

        if (! $tenant) {
            abort(404, 'Tenant nao encontrado');
        }

        $requestedPlan = null;
        if ($request->filled('requested_plan_id')) {
            $requestedPlan = Plan::find($request->input('requested_plan_id'));
        }

        $isTestPlan = (bool) $requestedPlan?->isTest();
        $paymentMethodService = app(PaymentMethodAvailabilityService::class);
        $enabledMethods = $paymentMethodService->enabledMethods();

        $validated = $request->validate([
            'requested_plan_id' => 'required|uuid|exists:plans,id',
            'requested_payment_method' => [
                $isTestPlan ? 'nullable' : 'required',
                Rule::in($enabledMethods),
            ],
            'reason' => 'nullable|string|max:1000',
        ], [
            'requested_plan_id.required' => 'Selecione um plano.',
            'requested_plan_id.exists' => 'O plano selecionado nao existe.',
            'requested_payment_method.required' => 'Selecione uma forma de pagamento.',
            'requested_payment_method.in' => 'Forma de pagamento indisponível no momento.',
            'reason.max' => 'O motivo nao pode ter mais de 1000 caracteres.',
        ]);

        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $subscription) {
            return back()->withErrors(['error' => 'Nenhuma assinatura encontrada.']);
        }

        $isTrialConversionContext = (bool) $subscription->is_trial
            || (! $tenant->isEligibleForAccess() && (bool) $tenant->expiredTrialSubscription());

        $pendingRequest = PlanChangeRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return back()->withErrors(['error' => 'Voce ja possui uma solicitacao pendente.']);
        }

        if ($subscription->plan_id === $validated['requested_plan_id']) {
            return back()->withErrors(['error' => 'O plano solicitado e o mesmo do atual.']);
        }

        if ($isTrialConversionContext && $requestedPlan?->isTest()) {
            return back()->withErrors(['requested_plan_id' => 'Nao e permitido converter trial para plano de teste.']);
        }

        if (! $isTestPlan && ! in_array($validated['requested_payment_method'], $enabledMethods, true)) {
            return back()->withErrors(['requested_payment_method' => 'Forma de pagamento indisponível no momento.'])->withInput();
        }

        try {
            PlanChangeRequest::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'current_plan_id' => $subscription->plan_id,
                'requested_plan_id' => $validated['requested_plan_id'],
                'requested_payment_method' => $isTestPlan ? null : $validated['requested_payment_method'],
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            Log::info('Solicitacao de mudanca de plano criada', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'requested_plan_id' => $validated['requested_plan_id'],
                'requested_plan_type' => $requestedPlan?->plan_type,
            ]);

            return redirect()
                ->route('tenant.subscription.show', ['slug' => $tenant->subdomain])
                ->with('success', 'Solicitacao de mudanca de plano enviada com sucesso! Aguarde a aprovacao do administrador.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar solicitacao de mudanca de plano', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenant->id,
            ]);

            return back()->withErrors(['error' => 'Erro ao processar solicitacao. Tente novamente.']);
        }
    }
}
