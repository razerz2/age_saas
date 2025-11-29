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
            'question_id' => ['required', 'exists:tenant.form_questions,id'],
            'value'       => ['nullable'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'question_id.required' => 'A pergunta é obrigatória.',
            'question_id.exists' => 'A pergunta selecionada não existe.',
        ];
    }
}
