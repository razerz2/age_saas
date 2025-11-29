<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'  => ['required', 'string', 'max:255'],
            'cpf'        => ['required', 'string', 'max:14', Rule::unique('tenant.patients', 'cpf')],
            'birth_date' => ['nullable', 'date'],
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('tenant.patients', 'email')],
            'phone'      => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'O nome completo é obrigatório.',
            'full_name.string' => 'O nome completo deve ser uma string válida.',
            'full_name.max' => 'O nome completo não pode ter mais que 255 caracteres.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser uma string válida.',
            'cpf.max' => 'O CPF não pode ter mais que 14 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado na clínica.',

            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',

            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado na clínica.',

            'phone.string' => 'O telefone deve ser uma string válida.',
            'phone.max' => 'O telefone não pode ter mais que 20 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove formatação do CPF e telefone antes da validação
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', $this->cpf),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D/', '', $this->phone),
            ]);
        }
    }
}

