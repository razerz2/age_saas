<?php

namespace App\Http\Requests\Tenant\Responses;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormResponseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id'       => ['required', 'exists:tenant.patients,id'],
            'appointment_id'   => ['nullable', 'exists:tenant.appointments,id'],
            'submitted_at'     => ['nullable', 'date'],
            'status'           => ['required', 'in:pending,submitted'],

            // answers = [question_id => value]
            'answers'          => ['nullable', 'array'],
            'answers.*'        => ['nullable'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'patient_id.required' => 'O paciente é obrigatório.',
            'patient_id.exists' => 'O paciente selecionado não existe.',

            'appointment_id.exists' => 'O agendamento selecionado não existe.',

            'submitted_at.date' => 'A data de submissão deve ser uma data válida.',

            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "pendente" ou "submetido".',

            'answers.array' => 'As respostas devem ser passadas como um array.',
        ];
    }
}
