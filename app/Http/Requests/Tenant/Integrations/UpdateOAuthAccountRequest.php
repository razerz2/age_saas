<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOAuthAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'access_token'   => ['required', 'string'],
            'refresh_token'  => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'access_token.required' => 'O token de acesso é obrigatório.',
            'access_token.string' => 'O token de acesso deve ser uma string válida.',

            'refresh_token.string' => 'O token de atualização deve ser uma string válida.',

            'expires_at.date' => 'A data de expiração deve ser uma data válida.',
        ];
    }
}
