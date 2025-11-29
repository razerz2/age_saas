<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalSpecialtyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tenant.medical_specialties,name'],
            'code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome da especialidade é obrigatório.',
            'name.string' => 'O nome da especialidade deve ser uma string válida.',
            'name.max' => 'O nome da especialidade não pode ter mais que 255 caracteres.',
            'name.unique' => 'Esta especialidade já está cadastrada.',

            'code.string' => 'O código deve ser uma string válida.',
            'code.max' => 'O código não pode ter mais que 50 caracteres.',
        ];
    }
}
