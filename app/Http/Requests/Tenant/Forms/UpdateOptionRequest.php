<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'label'    => ['required', 'string', 'max:255'],
            'value'    => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'label.required' => 'O rótulo da opção é obrigatório.',
            'label.string' => 'O rótulo da opção deve ser uma string válida.',
            'label.max' => 'O rótulo da opção não pode ter mais que 255 caracteres.',

            'value.required' => 'O valor da opção é obrigatório.',
            'value.string' => 'O valor da opção deve ser uma string válida.',
            'value.max' => 'O valor da opção não pode ter mais que 255 caracteres.',

            'position.integer' => 'A posição deve ser um número inteiro.',
            'position.min' => 'A posição deve ser no mínimo 0.',
        ];
    }
}
