<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'key'        => ['required', 'string', 'max:255', 'unique:integrations,key'],
            'is_enabled' => ['required', 'boolean'],
            'config'     => ['nullable', 'array'],
        ];
    }
}
