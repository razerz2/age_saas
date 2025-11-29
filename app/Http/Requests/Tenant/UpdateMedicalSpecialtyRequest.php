<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalSpecialtyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('tenant.medical_specialties', 'name')->ignore($id)],
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
