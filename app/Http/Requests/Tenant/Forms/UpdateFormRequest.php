<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'specialty_id' => ['nullable', 'exists:medical_specialties,id'],
            'doctor_id'    => ['nullable', 'exists:doctors,id'],
            'is_active'    => ['required', 'boolean'],
        ];
    }
}
