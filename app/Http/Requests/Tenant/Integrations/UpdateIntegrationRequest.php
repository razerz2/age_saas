<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIntegrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'key'        => ['required', 'string', 'max:255', Rule::unique('tenant.integrations', 'key')->ignore($id)],
            'is_enabled' => ['required', 'boolean'],
            'config'     => ['nullable', 'array'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'key.required' => 'A chave da integração é obrigatória.',
            'key.string' => 'A chave da integração deve ser uma string válida.',
            'key.max' => 'A chave da integração não pode ter mais que 255 caracteres.',
            'key.unique' => 'Esta chave de integração já está cadastrada.',

            'is_enabled.required' => 'O campo "Habilitado" é obrigatório.',
            'is_enabled.boolean' => 'O campo "Habilitado" deve ser verdadeiro ou falso.',

            'config.array' => 'A configuração deve ser passada como um array.',
        ];
    }
}
