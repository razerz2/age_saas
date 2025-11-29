<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id'  => [
                'required', 
                'exists:tenant.doctors,id',
                function ($attribute, $value, $fail) {
                    // Verifica se o médico já possui um calendário
                    $doctor = \App\Models\Tenant\Doctor::find($value);
                    if ($doctor && $doctor->calendars()->exists()) {
                        $fail('Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.');
                    }
                },
            ],
            'name'       => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'doctor_id.required' => 'O médico é obrigatório.',
            'doctor_id.exists' => 'O médico selecionado não existe.',

            'name.required' => 'O nome do calendário é obrigatório.',
            'name.string' => 'O nome do calendário deve ser uma string válida.',
            'name.max' => 'O nome do calendário não pode ter mais que 255 caracteres.',

            'external_id.string' => 'O ID externo deve ser uma string válida.',
            'external_id.max' => 'O ID externo não pode ter mais que 255 caracteres.',
        ];
    }
}
