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

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            // Esta request não possui validações obrigatórias, mas mantém o padrão de mensagens
        ];
    }
}
