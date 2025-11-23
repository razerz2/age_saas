<?php

namespace App\Http\Requests\Tenant\Integrations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('integration')->id;

        return [
            'key'        => ['required', 'string', 'max:255', "unique:integrations,key,{$id}"],
            'is_enabled' => ['required', 'boolean'],
            'config'     => ['nullable', 'array'],
        ];
    }
}
