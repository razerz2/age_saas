<?php

namespace App\Http\Requests\Tenant\Responses;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormResponseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id'       => ['required', 'exists:patients,id'],
            'appointment_id'   => ['nullable', 'exists:appointments,id'],
            'submitted_at'     => ['nullable', 'date'],
            'status'           => ['required', 'in:pending,submitted'],

            // answers = [question_id => value]
            'answers'          => ['nullable', 'array'],
            'answers.*'        => ['nullable'],
        ];
    }
}
