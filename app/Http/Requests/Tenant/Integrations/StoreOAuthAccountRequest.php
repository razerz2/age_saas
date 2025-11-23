<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class StoreOAuthAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'integration_id' => ['required', 'exists:integrations,id'],
            'user_id'        => ['required', 'exists:users,id'],
            'access_token'   => ['required', 'string'],
            'refresh_token'  => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ];
    }
}
