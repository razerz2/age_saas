<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'patient_id' => ['required', 'exists:tenant.patients,id'],
            'doctor_id' => ['required', 'exists:tenant.doctors,id'],
            'appointment_type_id' => ['nullable', 'exists:tenant.appointment_types,id'],
            'start_date' => ['required', 'date'],
            'end_type' => ['required', 'in:none,total_sessions,date'],
            'total_sessions' => ['nullable', 'integer', 'min:1', 'required_if:end_type,total_sessions'],
            'end_date' => ['nullable', 'date', 'after:start_date', 'required_if:end_type,date'],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.weekday' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'rules.*.time_slot' => ['nullable', 'string'],
            'rules.*.start_time' => ['required_without:rules.*.time_slot', 'date_format:H:i'],
            'rules.*.end_time' => ['required_without:rules.*.time_slot', 'date_format:H:i', 'after:rules.*.start_time'],
            'rules.*.frequency' => ['nullable', 'in:weekly,biweekly,monthly'],
            'rules.*.interval' => ['nullable', 'integer', 'min:1'],
        ];

        // Aplicar validação de appointment_mode baseado na configuração
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'user_choice') {
            $rules['appointment_mode'] = ['required', 'in:presencial,online'];
        } else {
            $rules['appointment_mode'] = ['nullable'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'patient_id.required' => 'O paciente é obrigatório.',
            'doctor_id.required' => 'O médico é obrigatório.',
            'start_date.required' => 'A data inicial é obrigatória.',
            'end_type.required' => 'O tipo de término é obrigatório.',
            'total_sessions.required_if' => 'O número total de sessões é obrigatório quando o tipo de término é "Total de sessões".',
            'end_date.required_if' => 'A data final é obrigatória quando o tipo de término é "Data final".',
            'end_date.after' => 'A data final deve ser posterior à data inicial.',
            'rules.required' => 'É necessário pelo menos uma regra de recorrência.',
            'rules.min' => 'É necessário pelo menos uma regra de recorrência.',
            'rules.*.weekday.required' => 'O dia da semana é obrigatório.',
            'rules.*.start_time.required' => 'A hora de início é obrigatória.',
            'rules.*.end_time.required' => 'A hora de término é obrigatória.',
            'rules.*.end_time.after' => 'A hora de término deve ser posterior à hora de início.',
            'rules.*.frequency.required' => 'A frequência é obrigatória.',
        ];
    }
}

