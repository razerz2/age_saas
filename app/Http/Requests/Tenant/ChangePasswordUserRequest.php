<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Auth;

class ChangePasswordUserRequest extends FormRequest
{
    public function authorize()
    {
        $authUser = Auth::guard('tenant')->user();
        if (!$authUser) {
            return false;
        }

        $targetUserId = (int) $this->route('id');
        if ($authUser->id === $targetUserId) {
            return true;
        }

        return $authUser->role === 'admin';
    }

    public function rules()
    {
        $authUser = Auth::guard('tenant')->user();
        $targetUserId = (int) $this->route('id');
        $isSelfPasswordChange = $authUser && $authUser->id === $targetUserId;

        return [
            'current_password' => $isSelfPasswordChange ? 'required|string' : 'nullable|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'A senha atual é obrigatória para alterar a própria senha.',
            'current_password.string' => 'A senha atual deve ser um texto válido.',

            'new_password.required' => 'A nova senha é obrigatória.',
            'new_password.string' => 'A nova senha deve ser um texto válido.',
            'new_password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'new_password.confirmed' => 'A confirmação da nova senha não coincide.',
        ];
    }
}
