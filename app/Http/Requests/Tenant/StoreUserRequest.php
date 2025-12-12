<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $user = auth()->guard('tenant')->user();
        $rules = [
            'name'       => ['required', 'string', 'max:255'],
            'name_full'  => ['required', 'string', 'max:255'],
            'telefone'   => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', Rule::unique('tenant.users', 'email')],
            'password'   => ['nullable', 'string', 'min:8', new StrongPassword()],
            'password_confirmation' => ['nullable', 'string'],
            'avatar'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_doctor' => ['nullable', 'boolean'],
            'role'       => ['required', 'in:admin,user,doctor'],
            'status'     => ['required', 'in:active,blocked'],
            'doctor_id'  => ['nullable', 'exists:tenant.doctors,id'],
        ];

        // Se o usuário logado não é médico nem admin, permite validar doctor_ids
        if ($user && $user->role !== 'doctor' && $user->role !== 'admin') {
            $rules['doctor_ids'] = ['nullable', 'array'];
            $rules['doctor_ids.*'] = ['exists:tenant.doctors,id'];
        }

        // Permite validar modules para todos os usuários
        $rules['modules'] = ['nullable', 'array'];

        return $rules;
    }

    /**
     * Validação adicional para confirmação de senha.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $password = $this->input('password');
            
            // Se a senha foi informada, valida a confirmação
            if (!empty($password)) {
                if ($password !== $this->input('password_confirmation')) {
                    $validator->errors()->add('password_confirmation', 'A confirmação da senha não coincide.');
                }
            }
        });
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome de exibição é obrigatório.',
            'name.string' => 'O nome de exibição deve ser uma string válida.',
            'name.max' => 'O nome de exibição não pode ter mais que 255 caracteres.',

            'name_full.required' => 'O nome completo é obrigatório.',
            'name_full.string' => 'O nome completo deve ser uma string válida.',
            'name_full.max' => 'O nome completo não pode ter mais que 255 caracteres.',

            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.string' => 'O telefone deve ser uma string válida.',
            'telefone.max' => 'O telefone não pode ter mais que 255 caracteres.',

            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',

            'avatar.image' => 'O arquivo deve ser uma imagem.',
            'avatar.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'avatar.max' => 'A imagem não pode ter mais de 2MB.',

            'is_doctor.boolean' => 'O campo "É médico?" deve ser verdadeiro ou falso.',

            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "ativo" ou "bloqueado".',

            'modules.array' => 'Os módulos devem ser passados como um array.',
        ];
    }
}
