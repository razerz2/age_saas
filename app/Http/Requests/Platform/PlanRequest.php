<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Platform\Plan;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $isTrialEnabled = $this->boolean('trial_enabled');
        $planType = $this->input('plan_type', Plan::TYPE_REAL);

        if ($planType === Plan::TYPE_TEST) {
            $isTrialEnabled = false;
        }

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

        $this->merge([
            'trial_enabled' => $isTrialEnabled,
            'trial_days' => $isTrialEnabled ? $this->input('trial_days') : null,
            'show_on_landing_page' => $this->boolean('show_on_landing_page'),
            'is_active' => $this->boolean('is_active'),
        ]);
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
            'plan_type' => ['required', 'in:real,test'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'show_on_landing_page' => ['boolean'],
            'trial_enabled' => ['boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365', 'required_if:trial_enabled,1'],
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
            'trial_days.required_if' => 'Informe a quantidade de dias do trial quando ele estiver habilitado.',
            'trial_days.min' => 'O trial deve ter pelo menos 1 dia.',
            'trial_days.max' => 'O trial nao pode ser maior que 365 dias.',
        ];
    }
}

