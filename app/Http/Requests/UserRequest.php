<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Platform\User;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir por enquanto, ajuste depois se quiser
        return true;
    }

    public function rules(): array
    {
        // Captura o parâmetro da rota (pode ser objeto User ou ID)
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : $routeUser;

        // Detecta se é atualização
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                // aqui garantimos que o $userId é sempre numérico
                'unique:users,email,' . ($userId ?? 'NULL'),
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'max:255',
                function ($attribute, $value, $fail) use ($isUpdate) {
                    // Só valida confirmação se o usuário realmente digitou uma senha nova
                    if (!$isUpdate || !empty($value)) {
                        if ($value !== $this->input('password_confirmation')) {
                            $fail('A confirmação da senha não confere.');
                        }
                    }
                },
            ],

            'status' => ['required', 'in:active,blocked'],
            'modules' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status informado é inválido.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            // se nenhum checkbox vier marcado, modules = []
            'modules' => $this->modules ?? [],
        ]);
    }
}
