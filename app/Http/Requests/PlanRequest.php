<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('price_cents')) {
            $valor = $this->price_cents;
            
            // Converte para string para análise
            $valorStr = (string) $valor;
            
            // Remove espaços
            $valorStr = trim($valorStr);
            
            // Substitui vírgula por ponto (padrão brasileiro: "49,90" -> "49.90")
            $valorStr = str_replace(',', '.', $valorStr);
            
            // Converte para float
            $valorFloat = floatval($valorStr);
            
            // Sempre trata como valor em reais e converte para centavos
            // Exemplos: 49.90 -> 4990 centavos, 49.9 -> 4990 centavos
            $centavos = (int) round($valorFloat * 100);
            
            $this->merge(['price_cents' => $centavos]);
        }
    }

    public function rules(): array
    {
        // Pega o ID do plano (pode ser um modelo ou ID direto)
        $plan = $this->route('plan');
        $planId = $plan ? (is_object($plan) ? $plan->id : $plan) : null;

        // Regra unique: ignora o próprio registro durante update
        $uniqueRule = Rule::unique('plans', 'name');
        if ($planId) {
            $uniqueRule->ignore($planId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:500'],
            'periodicity' => ['required', 'in:monthly,yearly,custom'],
            'period_months' => ['required', 'integer', 'min:1'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'in:commercial,contractual,sandbox'],
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
            'price_cents.integer' => 'O preço deve ser informado corretamente.',
            'features.array' => 'As funcionalidades devem estar no formato de lista (array).',
        ];
    }
}
