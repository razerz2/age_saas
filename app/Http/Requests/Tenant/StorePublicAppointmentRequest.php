<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Appointment;
use Carbon\Carbon;

class StorePublicAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'calendar_id'      => ['required', 'exists:tenant.calendars,id'],
            'appointment_type' => ['nullable', 'exists:tenant.appointment_types,id'],
            'specialty_id'     => ['nullable', 'exists:tenant.medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    /**
     * Validação adicional para verificar conflitos de horário
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startsAt = Carbon::parse($this->starts_at);
            $endsAt = Carbon::parse($this->ends_at);
            $calendarId = $this->calendar_id;

            // Obter patient_id da sessão (para agendamentos públicos)
            $patientId = \Illuminate\Support\Facades\Session::get('public_patient_id');

            // Verificar se o paciente já possui um agendamento no mesmo dia
            if ($patientId) {
                $existingAppointmentSameDay = Appointment::where('patient_id', $patientId)
                    ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->first();

                if ($existingAppointmentSameDay) {
                    $validator->errors()->add('starts_at', 'Você já possui um agendamento neste dia. Um paciente não pode ter dois agendamentos no mesmo dia.');
                    return;
                }
            }

            // Verificar se há conflito com agendamentos existentes (scheduled ou rescheduled)
            $conflictingAppointment = Appointment::where('calendar_id', $calendarId)
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->where(function($query) use ($startsAt, $endsAt) {
                    $query->where(function($q) use ($startsAt, $endsAt) {
                        $q->where('starts_at', '<', $endsAt)
                          ->where('ends_at', '>', $startsAt);
                    });
                })
                ->first();

            if ($conflictingAppointment) {
                $validator->errors()->add('starts_at', 'Este horário já está ocupado por outro agendamento. Por favor, selecione outro horário disponível.');
            }
        });
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages(): array
    {
        return [
            'calendar_id.required' => 'O calendário é obrigatório.',
            'calendar_id.exists' => 'O calendário selecionado não existe.',

            'appointment_type.exists' => 'O tipo de agendamento selecionado não existe.',

            'specialty_id.exists' => 'A especialidade selecionada não existe.',

            'starts_at.required' => 'A data e hora de início são obrigatórias.',
            'starts_at.date' => 'A data e hora de início devem ser uma data válida.',

            'ends_at.required' => 'A data e hora de fim são obrigatórias.',
            'ends_at.date' => 'A data e hora de fim devem ser uma data válida.',
            'ends_at.after' => 'A data e hora de fim deve ser posterior à data e hora de início.',

            'notes.string' => 'As observações devem ser uma string válida.',
        ];
    }
}

