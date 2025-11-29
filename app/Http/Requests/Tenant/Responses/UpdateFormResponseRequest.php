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

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'submitted_at.date' => 'A data de submissão deve ser uma data válida.',

            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "pendente" ou "submetido".',

            'answers.array' => 'As respostas devem ser passadas como um array.',
        ];
    }
}
