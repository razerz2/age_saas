<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class AddQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'section_id' => ['nullable', 'exists:tenant.form_sections,id'],
            'label'      => ['required', 'string', 'max:255'],
            'help_text'  => ['nullable', 'string'],
            'type'       => ['required', 'in:single_choice,multi_choice,text,number,date,boolean'],
            'required'   => ['nullable', 'boolean'],
            'position'   => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'section_id.exists' => 'A seção selecionada não existe.',

            'label.required' => 'O rótulo da pergunta é obrigatório.',
            'label.string' => 'O rótulo da pergunta deve ser uma string válida.',
            'label.max' => 'O rótulo da pergunta não pode ter mais que 255 caracteres.',

            'help_text.string' => 'O texto de ajuda deve ser uma string válida.',

            'type.required' => 'O tipo da pergunta é obrigatório.',
            'type.in' => 'O tipo da pergunta deve ser: escolha única, escolha múltipla, texto, número, data ou booleano.',

            'required.boolean' => 'O campo "Obrigatório" deve ser verdadeiro ou falso.',

            'position.integer' => 'A posição deve ser um número inteiro.',
            'position.min' => 'A posição deve ser no mínimo 0.',
        ];
    }
}
