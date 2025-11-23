<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name'  => ['required', 'string', 'max:255'],
            'cpf'        => ['required', 'string', 'max:14', 'unique:patients,cpf'],
            'birth_date' => ['nullable', 'date'],
            'email'      => ['nullable', 'email'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }
}
