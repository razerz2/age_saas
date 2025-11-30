<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Tenant\AppointmentType;

class StoreAppointmentTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id'   => ['required', 'uuid', 'exists:tenant.doctors,id'],
            'name'         => ['required', 'string', 'max:255'],
            'duration_min' => ['required', 'integer', 'min:1'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $doctorId = $this->input('doctor_id');
            
            if ($doctorId) {
                $existingAppointmentType = AppointmentType::where('doctor_id', $doctorId)->first();
                
                if ($existingAppointmentType) {
                    $validator->errors()->add(
                        'doctor_id',
                        'Este médico já possui um tipo de consulta registrado. Cada médico só pode ter um tipo de consulta.'
                    );
                }
            }
        });
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'doctor_id.required' => 'O médico é obrigatório.',
            'doctor_id.uuid' => 'O ID do médico deve ser um UUID válido.',
            'doctor_id.exists' => 'O médico selecionado não existe.',
            'doctor_id.custom' => 'Este médico já possui um tipo de consulta registrado. Cada médico só pode ter um tipo de consulta.',

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
