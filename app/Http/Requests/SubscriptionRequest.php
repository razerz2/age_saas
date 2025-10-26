<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Platform\Subscription;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'      => ['required', 'uuid', 'exists:tenants,id'],
            'plan_id'        => ['required', 'uuid', 'exists:plans,id'],
            'starts_at'      => ['required', 'date'],
            'ends_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
            'due_day'        => ['required', 'integer', 'min:1', 'max:31'],
            'status'         => ['required', 'in:active,past_due,canceled,trialing,pending'],
            'auto_renew'     => ['boolean'],
            'payment_method' => ['required', 'in:PIX,BOLETO,CREDIT_CARD,DEBIT_CARD'], // ✅ novo campo
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Selecione um tenant.',
            'tenant_id.exists'   => 'O tenant selecionado é inválido.',

            'plan_id.required'   => 'Selecione um plano.',
            'plan_id.exists'     => 'O plano selecionado é inválido.',

            'starts_at.required' => 'A data de início é obrigatória.',
            'ends_at.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            'due_day.required'   => 'O dia de vencimento é obrigatório.',
            'due_day.min'        => 'O dia de vencimento deve ser no mínimo 1.',
            'due_day.max'        => 'O dia de vencimento deve ser no máximo 31.',

            'status.required'    => 'O status é obrigatório.',
            'status.in'          => 'O status informado é inválido.',

            'payment_method.required' => 'Selecione o método de pagamento.',
            'payment_method.in'       => 'O método de pagamento informado é inválido.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tenantId = $this->tenant_id;

            if ($tenantId) {
                $alreadyHasActive = Subscription::where('tenant_id', $tenantId)
                    ->whereIn('status', ['active', 'trialing'])
                    ->exists();

                if ($alreadyHasActive) {
                    $validator->errors()->add(
                        'tenant_id',
                        'Este tenant já possui uma assinatura ativa ou em teste.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_renew' => $this->boolean('auto_renew'),
        ]);
    }
}
