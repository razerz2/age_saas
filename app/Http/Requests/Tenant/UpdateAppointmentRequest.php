<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use Carbon\Carbon;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'doctor_id'        => ['required', 'exists:tenant.doctors,id'],
            'calendar_id'      => ['nullable', 'exists:tenant.calendars,id'], // Será definido automaticamente
            'appointment_type' => ['nullable', 'exists:tenant.appointment_types,id'],
            'patient_id'       => ['required', 'exists:tenant.patients,id'],
            'specialty_id'     => ['nullable', 'exists:tenant.medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],

            'status'           => ['required', 'in:scheduled,rescheduled,canceled,attended,no_show'],
            'notes'            => ['nullable', 'string'],
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

    /**
     * Validação adicional para verificar conflitos de horário
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentId = $this->route('id');
            $startsAt = Carbon::parse($this->starts_at);
            $endsAt = Carbon::parse($this->ends_at);
            $startsAtCampoGrande = $startsAt->copy()->timezone('America/Campo_Grande');
            
            // Verificar se a data de início não é anterior à data/hora atual
            $now = Carbon::now('America/Campo_Grande');
            if ($startsAtCampoGrande->lt($now)) {
                $validator->errors()->add('starts_at', 'Não é possível agendar para uma data/hora passada. Por favor, selecione uma data/hora atual ou futura.');
                return;
            }
            
            // Obter doctor_id e buscar calendar_id automaticamente
            $doctorId = $this->doctor_id;
            $calendarId = $this->calendar_id;
            
            // Se não tiver calendar_id mas tiver doctor_id, buscar o calendário principal do médico
            if (!$calendarId && $doctorId) {
                $doctor = Doctor::find($doctorId);
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

            // Buscar o calendário para validação
            $calendar = Calendar::with('doctor')->find($calendarId);
            if (!$calendar || !$calendar->doctor) {
                $validator->errors()->add('doctor_id', 'O calendário do médico não foi encontrado.');
                return;
            }
            
            // Garantir que o calendar_id corresponde ao doctor_id
            if ($calendar->doctor_id !== $doctorId) {
                $validator->errors()->add('doctor_id', 'O calendário selecionado não pertence ao médico escolhido.');
                return;
            }
            $weekday = $startsAt->dayOfWeek; // 0 = Domingo, 6 = Sábado

            // Verificar se o médico atende no dia da semana selecionado
            $businessHours = BusinessHour::where('doctor_id', $doctorId)
                ->where('weekday', $weekday)
                ->get();

            if ($businessHours->isEmpty()) {
                $weekdayNames = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
                $validator->errors()->add('starts_at', 'O médico não realiza atendimento em ' . $weekdayNames[$weekday] . '. Por favor, selecione outro dia.');
                return;
            }

            // Verificar se o horário está dentro do horário de atendimento do médico
            $startTime = $startsAt->format('H:i:s');
            $endTime = $endsAt->format('H:i:s');
            $isWithinBusinessHours = false;

            foreach ($businessHours as $businessHour) {
                $bhStart = Carbon::parse($businessHour->start_time)->format('H:i:s');
                $bhEnd = Carbon::parse($businessHour->end_time)->format('H:i:s');

                // Verificar se o agendamento está dentro do horário de atendimento
                if ($startTime >= $bhStart && $endTime <= $bhEnd) {
                    // Verificar se não está dentro de um intervalo (se houver)
                    $isInBreak = false;
                    if ($businessHour->break_start_time && $businessHour->break_end_time) {
                        $breakStart = Carbon::parse($businessHour->break_start_time)->format('H:i:s');
                        $breakEnd = Carbon::parse($businessHour->break_end_time)->format('H:i:s');
                        
                        // Verifica se o agendamento se sobrepõe ao intervalo
                        $isInBreak = ($startTime < $breakEnd && $endTime > $breakStart);
                    }

                    if (!$isInBreak) {
                        $isWithinBusinessHours = true;
                        break;
                    }
                }
            }

            if (!$isWithinBusinessHours) {
                $validator->errors()->add('starts_at', 'O horário selecionado está fora do horário de atendimento do médico. Por favor, selecione um horário dentro do horário de atendimento.');
                return;
            }

            // Verificar se o paciente já possui outro agendamento no mesmo dia (excluindo o atual)
            if ($this->patient_id) {
                $existingAppointmentSameDay = Appointment::where('patient_id', $this->patient_id)
                    ->where('id', '!=', $appointmentId) // Excluir o próprio agendamento
                    ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->first();

                if ($existingAppointmentSameDay) {
                    $validator->errors()->add('starts_at', 'Este paciente já possui outro agendamento neste dia. Um paciente não pode ter dois agendamentos no mesmo dia.');
                    return;
                }
            }

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
