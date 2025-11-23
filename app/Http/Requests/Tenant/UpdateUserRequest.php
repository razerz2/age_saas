<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Permite que qualquer usuário possa fazer a solicitação, você pode ajustar isso conforme necessário
    }

    public function rules()
    {
        $userId = $this->route('user')->id; // Obtém o ID do usuário da rota

        return [
            'name'       => ['required', 'string', 'max:255'],
            'name_full'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', "unique:users,email,{$userId}"],
            'status'     => ['required', 'in:active,blocked'],
            'modules'    => ['nullable', 'array'], // Valida que os módulos sejam um array
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome completo é obrigatório.',
            'name.string' => 'O nome completo deve ser uma string válida.',
            'name.max' => 'O nome completo não pode ter mais que 255 caracteres.',
            'name_full.required' => 'O nome de exibição é obrigatório.',
            'name_full.string' => 'O nome de exibição deve ser uma string válida.',
            'name_full.max' => 'O nome de exibição não pode ter mais que 255 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "ativo" ou "bloqueado".',
            'modules.array' => 'Os módulos devem ser passados como um array.',
        ];
    }
}
