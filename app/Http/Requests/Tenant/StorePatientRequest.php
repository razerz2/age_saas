<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name'  => ['required', 'string', 'max:255'],
            'cpf'        => ['required', 'string', 'max:14', Rule::unique('tenant.patients', 'cpf')],
            'birth_date' => ['nullable', 'date'],
            'email'      => ['nullable', 'email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'full_name.required' => 'O nome completo é obrigatório.',
            'full_name.string' => 'O nome completo deve ser uma string válida.',
            'full_name.max' => 'O nome completo não pode ter mais que 255 caracteres.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser uma string válida.',
            'cpf.max' => 'O CPF não pode ter mais que 14 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',

            'email.email' => 'Por favor, insira um e-mail válido.',

            'phone.string' => 'O telefone deve ser uma string válida.',
            'phone.max' => 'O telefone não pode ter mais que 20 caracteres.',

            'is_active.boolean' => 'O campo "Ativo" deve ser verdadeiro ou falso.',
        ];
    }
}
