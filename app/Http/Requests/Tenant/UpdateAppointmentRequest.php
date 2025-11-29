<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Appointment;
use Carbon\Carbon;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'calendar_id'      => ['required', 'exists:tenant.calendars,id'],
            'appointment_type' => ['nullable', 'exists:tenant.appointment_types,id'],
            'patient_id'       => ['required', 'exists:tenant.patients,id'],
            'specialty_id'     => ['nullable', 'exists:tenant.medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],

            'status'           => ['required', 'in:scheduled,rescheduled,canceled,attended,no_show'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    /**
     * Validação adicional para verificar conflitos de horário
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentId = $this->route('id');
            $startsAt = Carbon::parse($this->starts_at);
            $endsAt = Carbon::parse($this->ends_at);
            $calendarId = $this->calendar_id;

            // Verificar se há conflito com agendamentos existentes (scheduled ou rescheduled)
            // Excluindo o próprio agendamento que está sendo editado
            $conflictingAppointment = Appointment::where('calendar_id', $calendarId)
                ->where('id', '!=', $appointmentId)
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->where(function($query) use ($startsAt, $endsAt) {
                    $query->where(function($q) use ($startsAt, $endsAt) {
                        // Verifica sobreposição: novo agendamento começa antes do existente terminar
                        // e novo agendamento termina depois do existente começar
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
    public function messages()
    {
        return [
            'calendar_id.required' => 'O calendário é obrigatório.',
            'calendar_id.exists' => 'O calendário selecionado não existe.',

            'appointment_type.exists' => 'O tipo de agendamento selecionado não existe.',

            'patient_id.required' => 'O paciente é obrigatório.',
            'patient_id.exists' => 'O paciente selecionado não existe.',

            'specialty_id.exists' => 'A especialidade selecionada não existe.',

            'starts_at.required' => 'A data e hora de início são obrigatórias.',
            'starts_at.date' => 'A data e hora de início devem ser uma data válida.',

            'ends_at.required' => 'A data e hora de fim são obrigatórias.',
            'ends_at.date' => 'A data e hora de fim devem ser uma data válida.',
            'ends_at.after' => 'A data e hora de fim deve ser posterior à data e hora de início.',

            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: agendado, reagendado, cancelado, atendido ou não compareceu.',

            'notes.string' => 'As observações devem ser uma string válida.',
        ];
    }
}
