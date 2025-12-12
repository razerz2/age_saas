<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StrongPassword;

class ChangePasswordUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Permite qualquer usuário autorizado (você pode adicionar mais condições de autorização aqui, se necessário)
    }

    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.string' => 'A senha atual deve ser uma string válida.',

            'new_password.required' => 'A nova senha é obrigatória.',
            'new_password.string' => 'A nova senha deve ser uma string válida.',
            'new_password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'new_password.confirmed' => 'A confirmação da nova senha não coincide.',
        ];
    }
}
