<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Platform\User;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : $routeUser;

        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name'       => ['required', 'string', 'max:255'],
            'name_full'  => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . ($userId ?? 'NULL'),
            ],
            'password'   => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'max:255',
            ],
            'status'  => ['required', 'in:active,blocked'],
            'modules' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
            $password = $this->input('password');

            if ((!$isUpdate || !empty($password)) &&
                $password !== $this->input('password_confirmation')) {
                $validator->errors()->add('password', 'A confirmação da senha não confere.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'O Apelido é obrigatório.',
            'name_full.required'   => 'O Nome completo é obrigatório.',
            'email.required'       => 'O e-mail é obrigatório.',
            'email.email'          => 'Informe um e-mail válido.',
            'email.unique'         => 'Este e-mail já está em uso.',
            'password.required'    => 'A senha é obrigatória.',
            'password.min'         => 'A senha deve ter pelo menos 6 caracteres.',
            'status.required'      => 'O status é obrigatório.',
            'status.in'            => 'O status informado é inválido.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'modules' => $this->modules ?? [],
        ]);
    }
}
