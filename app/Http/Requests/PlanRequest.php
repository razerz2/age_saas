<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ Permite que qualquer usuário autenticado acesse (ajuste se necessário)
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan'); // usado no update

        return [
            'name' => ['required', 'string', 'max:255', 'unique:plans,name,' . $planId],
            'periodicity' => ['required', 'in:monthly,yearly,custom'],
            'period_months' => ['required', 'integer', 'min:1'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do plano é obrigatório.',
            'name.unique' => 'Já existe um plano com esse nome.',
            'periodicity.required' => 'A periodicidade é obrigatória.',
            'periodicity.in' => 'A periodicidade deve ser mensal, anual ou personalizada.',
            'period_months.required' => 'Informe a duração em meses do plano.',
            'period_months.integer' => 'O campo de duração deve ser um número inteiro.',
            'price_cents.required' => 'O preço é obrigatório.',
            'price_cents.integer' => 'O preço deve ser informado em centavos (inteiro).',
            'features.array' => 'As funcionalidades devem estar no formato de lista (array).',
        ];
    }
}
