<?php

namespace App\Http\Requests\Tenant\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cash,bank,pix,credit'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da conta é obrigatório.',
            'type.required' => 'O tipo da conta é obrigatório.',
            'type.in' => 'O tipo deve ser: dinheiro, banco, PIX ou crédito.',
            'initial_balance.required' => 'O saldo inicial é obrigatório.',
            'initial_balance.numeric' => 'O saldo inicial deve ser um número.',
            'initial_balance.min' => 'O saldo inicial não pode ser negativo.',
        ];
    }
}

