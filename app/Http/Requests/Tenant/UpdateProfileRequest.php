<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $user = auth()->guard('tenant')->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_full' => ['required', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('tenant.users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', new StrongPassword()],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome de exibição é obrigatório.',
            'name_full.required' => 'O nome completo é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password_confirmation.same' => 'A confirmação da senha não coincide.',
            'avatar.image' => 'O arquivo deve ser uma imagem.',
            'avatar.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'avatar.max' => 'A imagem não pode ter mais de 2MB.',
        ];
    }
}

