<?php

namespace App\Http\Requests\Tenant\Responses;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponseAnswerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'question_id' => ['required', 'exists:form_questions,id'],
            'value'       => ['nullable'],
        ];
    }
}
