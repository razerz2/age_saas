<?php

namespace App\Http\Requests\Tenant\Responses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormResponseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'submitted_at' => ['nullable', 'date'],
            'status'       => ['required', 'in:pending,submitted'],

            'answers'      => ['nullable', 'array'],
            'answers.*'    => ['nullable'],
        ];
    }
}
