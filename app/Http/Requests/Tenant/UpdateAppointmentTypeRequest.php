<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'duration_min' => ['required', 'integer', 'min:1'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'name.required' => 'O nome do tipo de agendamento é obrigatório.',
            'name.string' => 'O nome do tipo de agendamento deve ser uma string válida.',
            'name.max' => 'O nome do tipo de agendamento não pode ter mais que 255 caracteres.',

            'duration_min.required' => 'A duração em minutos é obrigatória.',
            'duration_min.integer' => 'A duração em minutos deve ser um número inteiro.',
            'duration_min.min' => 'A duração em minutos deve ser no mínimo 1 minuto.',

            'is_active.required' => 'O campo "Ativo" é obrigatório.',
            'is_active.boolean' => 'O campo "Ativo" deve ser verdadeiro ou falso.',
        ];
    }
}
