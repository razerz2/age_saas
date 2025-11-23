<?php

namespace App\Http\Requests\Tenant\Responses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResponseAnswerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'value' => ['nullable'],
        ];
    }
}
