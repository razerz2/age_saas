<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Permite que qualquer usuário possa fazer a solicitação, você pode ajustar isso conforme necessário
    }

    public function rules()
    {
        $userId = $this->route('id'); // Obtém o ID do usuário da rota
        $user = auth()->guard('tenant')->user();

        $rules = [
            'name'       => ['required', 'string', 'max:255'],
            'name_full'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', Rule::unique('tenant.users', 'email')->ignore($userId)],
            'telefone'   => ['nullable', 'string', 'max:255'],
            'password'   => ['nullable', 'min:6'],
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

        // Se o usuário logado não é admin, permite validar modules
        if ($user && $user->role !== 'admin') {
            $rules['modules'] = ['nullable', 'array'];
        }

        return $rules;
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

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'telefone.string' => 'O telefone deve ser uma string válida.',
            'telefone.max' => 'O telefone não pode ter mais que 255 caracteres.',

            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',

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
