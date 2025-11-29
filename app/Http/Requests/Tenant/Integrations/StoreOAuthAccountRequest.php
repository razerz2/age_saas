<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class StoreOAuthAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'integration_id' => ['required', 'exists:tenant.integrations,id'],
            'user_id'        => ['required', 'exists:tenant.users,id'],
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
            'integration_id.required' => 'A integração é obrigatória.',
            'integration_id.exists' => 'A integração selecionada não existe.',

            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.exists' => 'O usuário selecionado não existe.',

            'access_token.required' => 'O token de acesso é obrigatório.',
            'access_token.string' => 'O token de acesso deve ser uma string válida.',

            'refresh_token.string' => 'O token de atualização deve ser uma string válida.',

            'expires_at.date' => 'A data de expiração deve ser uma data válida.',
        ];
    }
}
