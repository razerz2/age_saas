<?php

namespace App\Http\Requests\Tenant\Forms;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'doctor_id'    => ['required', 'exists:tenant.doctors,id'],
            'specialty_id' => ['nullable', 'exists:tenant.medical_specialties,id'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome do formulário é obrigatório.',
            'name.string' => 'O nome do formulário deve ser uma string válida.',
            'name.max' => 'O nome do formulário não pode ter mais que 255 caracteres.',

            'description.string' => 'A descrição deve ser uma string válida.',

            'doctor_id.required' => 'O médico é obrigatório.',
            'doctor_id.exists' => 'O médico selecionado não existe.',

            'specialty_id.exists' => 'A especialidade selecionada não existe.',

            'is_active.required' => 'O campo "Ativo" é obrigatório.',
            'is_active.boolean' => 'O campo "Ativo" deve ser verdadeiro ou falso.',
        ];
    }
}
