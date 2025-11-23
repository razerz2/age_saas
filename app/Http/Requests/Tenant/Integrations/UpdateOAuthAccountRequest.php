<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOAuthAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'access_token'   => ['required', 'string'],
            'refresh_token'  => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ];
    }
}
