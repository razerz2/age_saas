<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgendaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'uuid', 'exists:tenant.doctors,id'],
            'name' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'in:0,1'],

            'business_hours' => ['required', 'array', 'min:1'],
            'business_hours.*.weekday' => ['required', 'integer', 'min:0', 'max:6', 'distinct'],
            'business_hours.*.start_time' => ['required', 'date_format:H:i'],
            'business_hours.*.end_time' => ['required', 'date_format:H:i'],
            'business_hours.*.break_start_time' => ['nullable', 'date_format:H:i'],
            'business_hours.*.break_end_time' => ['nullable', 'date_format:H:i'],

            'appointment_types' => ['required', 'array', 'min:1'],
            'appointment_types.*.id' => ['nullable', 'uuid', 'exists:tenant.appointment_types,id'],
            'appointment_types.*.name' => ['required', 'string', 'max:255'],
            'appointment_types.*.duration_min' => ['required', 'integer', 'min:1'],
            'appointment_types.*.is_active' => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Selecione o profissional da agenda.',
            'doctor_id.exists' => 'Profissional inválido.',
            'name.required' => 'Informe o nome da agenda.',
            'is_active.required' => 'Informe o status da agenda.',
            'business_hours.required' => 'Cadastre ao menos um horário de atendimento.',
            'business_hours.*.weekday.distinct' => 'Já existe horário cadastrado para este dia da semana.',
            'appointment_types.required' => 'Cadastre ao menos um tipo de atendimento.',
        ];
    }
}
