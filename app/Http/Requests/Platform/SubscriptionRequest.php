<?php

namespace App\Http\Requests\Platform;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $plan = null;
        if ($this->filled('plan_id')) {
            $plan = Plan::query()->find($this->input('plan_id'));
        }

        $isTestPlan = (bool) $plan?->isTest();

        return [
            'tenant_id' => ['required', 'uuid', 'exists:tenants,id'],
            'plan_id' => ['required', 'uuid', 'exists:plans,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'conversion_from_trial' => ['nullable', 'boolean'],
            'due_day' => [$isTestPlan ? 'nullable' : 'required', 'integer', 'min:1', 'max:31'],
            'status' => [$isTestPlan ? 'nullable' : 'required', 'in:active,past_due,canceled,trialing,pending'],
            'auto_renew' => ['boolean'],
            'payment_method' => [$isTestPlan ? 'nullable' : 'required', 'in:PIX,BOLETO,CREDIT_CARD,DEBIT_CARD'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Selecione um tenant.',
            'tenant_id.exists' => 'O tenant selecionado e invalido.',

            'plan_id.required' => 'Selecione um plano.',
            'plan_id.exists' => 'O plano selecionado e invalido.',

            'starts_at.required' => 'A data de inicio e obrigatoria.',
            'ends_at.after_or_equal' => 'A data de termino deve ser igual ou posterior a data de inicio.',

            'due_day.required' => 'O dia de vencimento e obrigatorio.',
            'due_day.min' => 'O dia de vencimento deve ser no minimo 1.',
            'due_day.max' => 'O dia de vencimento deve ser no maximo 31.',

            'status.required' => 'O status e obrigatorio.',
            'status.in' => 'O status informado e invalido.',

            'payment_method.required' => 'Selecione o metodo de pagamento.',
            'payment_method.in' => 'O metodo de pagamento informado e invalido.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tenantId = $this->tenant_id;
            $conversionFromTrial = $this->boolean('conversion_from_trial');
            $currentSubscription = $this->route('subscription');
            $currentSubscriptionId = $currentSubscription instanceof Subscription
                ? $currentSubscription->id
                : $currentSubscription;
            $selectedPlan = $this->filled('plan_id')
                ? Plan::query()->find($this->plan_id)
                : null;

            if ($conversionFromTrial && $selectedPlan?->isTest()) {
                $validator->errors()->add(
                    'plan_id',
                    'A conversao de trial nao permite planos de teste.'
                );

                return;
            }

            if ($tenantId) {
                $activeSubscriptionQuery = Subscription::where('tenant_id', $tenantId)
                    ->whereIn('status', ['active', 'trialing']);

                if ($conversionFromTrial) {
                    $activeSubscriptionQuery->where(function ($query) {
                        $query->whereNull('is_trial')
                            ->orWhere('is_trial', false);
                    });
                }

                if (!empty($currentSubscriptionId)) {
                    $activeSubscriptionQuery->where('id', '!=', $currentSubscriptionId);
                }

                $alreadyHasActive = $activeSubscriptionQuery->exists();

                if ($alreadyHasActive) {
                    $validator->errors()->add(
                        'tenant_id',
                        'Este tenant ja possui uma assinatura ativa ou em teste.'
                    );
                    return;
                }

                $tenant = Tenant::find($tenantId);

                if (
                    $tenant &&
                    in_array($tenant->asaas_sync_status, ['failed'], true)
                ) {
                    $validator->errors()->add(
                        'tenant_id',
                        'Nao e possivel criar a assinatura: o tenant esta pendente ou com erro de sincronizacao no Asaas. Corrija o sincronismo antes de prosseguir.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_renew' => $this->boolean('auto_renew'),
            'conversion_from_trial' => $this->boolean('conversion_from_trial'),
        ]);
    }
}
