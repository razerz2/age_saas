<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'duration_min' => ['required', 'integer', 'min:1'],
            'is_active'    => ['required', 'boolean'],
        ];
    }
}
