<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id'       => ['required', 'exists:users,id'],
            'crm_number'    => ['nullable', 'string', 'max:50'],
            'crm_state'     => ['nullable', 'string', 'max:2'],
            'signature'     => ['nullable', 'string', 'max:255'],
            'specialties'   => ['nullable', 'array'],
            'specialties.*' => ['uuid', 'exists:medical_specialties,id'],
        ];
    }
}
