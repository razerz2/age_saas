<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id'  => ['required', 'exists:doctors,id'],
            'name'       => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
