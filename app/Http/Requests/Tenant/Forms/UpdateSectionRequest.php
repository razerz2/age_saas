<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'    => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'title.string' => 'O título da seção deve ser uma string válida.',
            'title.max' => 'O título da seção não pode ter mais que 255 caracteres.',

            'position.integer' => 'A posição deve ser um número inteiro.',
            'position.min' => 'A posição deve ser no mínimo 0.',
        ];
    }
}
