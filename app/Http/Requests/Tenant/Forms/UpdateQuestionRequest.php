<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'section_id' => ['nullable', 'exists:form_sections,id'],
            'label'      => ['required', 'string', 'max:255'],
            'help_text'  => ['nullable', 'string'],
            'type'       => ['required', 'in:single_choice,multi_choice,text,number,date,boolean'],
            'required'   => ['nullable', 'boolean'],
            'position'   => ['nullable', 'integer', 'min:0'],
        ];
    }
}
