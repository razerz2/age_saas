<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use Carbon\Carbon;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id'        => ['required', 'exists:tenant.doctors,id'],
            'calendar_id'      => ['nullable', 'exists:tenant.calendars,id'], // Opcional, será definido automaticamente
            'appointment_type' => ['nullable', 'exists:tenant.appointment_types,id'],
            'patient_id'       => ['required', 'exists:tenant.patients,id'],
            'specialty_id'     => ['nullable', 'exists:tenant.medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],

            'status'           => ['nullable', 'in:scheduled,rescheduled,canceled,attended,no_show'],
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
            
            // Obter calendar_id do request ou buscar do médico
            $calendarId = $this->calendar_id;
            if (!$calendarId && $this->doctor_id) {
                $doctor = \App\Models\Tenant\Doctor::find($this->doctor_id);
                if ($doctor) {
                    $calendar = $doctor->getPrimaryCalendar();
                    if ($calendar) {
                        $calendarId = $calendar->id;
                    }
                }
            }

            if (!$calendarId) {
                $validator->errors()->add('doctor_id', 'O médico selecionado não possui um calendário cadastrado.');
                return;
            }

            // Verificar se o paciente já possui um agendamento no mesmo dia com o mesmo médico
            if ($this->patient_id && $this->doctor_id) {
                // Verificar agendamentos normais
                $existingAppointmentSameDay = Appointment::where('patient_id', $this->patient_id)
                    ->whereHas('calendar', function($query) {
                        $query->where('doctor_id', $this->doctor_id);
                    })
                    ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->first();

                if ($existingAppointmentSameDay) {
                    $validator->errors()->add('starts_at', 'Você já possui um agendamento neste dia com este médico. Um paciente não pode ter dois agendamentos no mesmo dia com o mesmo médico.');
                    return;
                }

                // Verificar agendamentos recorrentes ativos que já geraram agendamentos neste dia
                $recurringGeneratedAppointment = Appointment::where('patient_id', $this->patient_id)
                    ->whereHas('calendar', function($query) {
                        $query->where('doctor_id', $this->doctor_id);
                    })
                    ->whereNotNull('recurring_appointment_id')
                    ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->first();

                if ($recurringGeneratedAppointment) {
                    $validator->errors()->add('starts_at', 'Você já possui um agendamento recorrente neste dia com este médico. Um paciente não pode ter dois agendamentos no mesmo dia com o mesmo médico.');
                    return;
                }

                // Verificar se existe uma recorrência ativa que VAI gerar um agendamento nesta data
                $weekdayString = RecurringAppointmentRule::weekdayFromNumber($startsAt->dayOfWeek);
                $activeRecurringAppointment = RecurringAppointment::where('patient_id', $this->patient_id)
                    ->where('doctor_id', $this->doctor_id)
                    ->where('active', true)
                    ->where('start_date', '<=', $startsAt->format('Y-m-d'))
                    ->where(function($query) use ($startsAt) {
                        $query->where('end_type', 'none')
                            ->orWhere(function($q) use ($startsAt) {
                                $q->where('end_type', 'date')
                                  ->where('end_date', '>=', $startsAt->format('Y-m-d'));
                            })
                            ->orWhere('end_type', 'total_sessions');
                    })
                    ->whereHas('rules', function($query) use ($weekdayString) {
                        $query->where('weekday', $weekdayString);
                    })
                    ->get();

                foreach ($activeRecurringAppointment as $recurring) {
                    // Verificar se a data está dentro dos limites da recorrência
                    if ($startsAt->lt($recurring->start_date)) {
                        continue; // Data antes do início da recorrência
                    }

                    if ($recurring->end_type === 'date' && $recurring->end_date && $startsAt->gt($recurring->end_date)) {
                        continue; // Data depois do fim da recorrência
                    }

                    // Verificar se já atingiu o limite de sessões (se aplicável)
                    if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
                        $generatedCount = $recurring->getGeneratedSessionsCount();
                        if ($generatedCount >= $recurring->total_sessions) {
                            continue; // Já atingiu o limite
                        }
                    }

                    // Verificar se já existe um agendamento gerado por esta recorrência nesta data
                    $existingRecurringAppointment = Appointment::where('recurring_appointment_id', $recurring->id)
                        ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                        ->first();

                    if (!$existingRecurringAppointment) {
                        // A recorrência vai gerar um agendamento nesta data
                        $validator->errors()->add('starts_at', 'Você já possui um agendamento recorrente ativo que gera agendamentos neste dia com este médico. Um paciente não pode ter dois agendamentos no mesmo dia com o mesmo médico.');
                        return;
                    }
                }
            }

            // Verificar se há conflito com agendamentos existentes (scheduled ou rescheduled)
            $conflictingAppointment = Appointment::where('calendar_id', $calendarId)
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
            'doctor_id.required' => 'O médico é obrigatório.',
            'doctor_id.exists' => 'O médico selecionado não existe.',

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
