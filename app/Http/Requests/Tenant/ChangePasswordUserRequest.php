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
            'current_password.required' => 'A senha atual e obrigatoria para alterar sua propria senha.',
            'current_password.string' => 'A senha atual deve ser uma string valida.',

            'new_password.required' => 'A nova senha e obrigatoria.',
            'new_password.string' => 'A nova senha deve ser uma string valida.',
            'new_password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'new_password.confirmed' => 'A confirmacao da nova senha nao coincide.',
        ];
    }
}
