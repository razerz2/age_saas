<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use Carbon\Carbon;

class StorePublicAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'calendar_id'      => ['required', 'exists:tenant.calendars,id'],
            'appointment_type' => ['nullable', 'exists:tenant.appointment_types,id'],
            'specialty_id'     => ['nullable', 'exists:tenant.medical_specialties,id'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],
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
            $startsAt = Carbon::parse($this->starts_at);
            $endsAt = Carbon::parse($this->ends_at);
            
            // Verificar se a data de início não é anterior à data/hora atual
            $now = Carbon::now();
            if ($startsAt->lt($now)) {
                $validator->errors()->add('starts_at', 'Não é possível agendar para uma data/hora passada. Por favor, selecione uma data/hora atual ou futura.');
                return;
            }
            
            $calendarId = $this->calendar_id;

            // Buscar o calendário e o médico
            $calendar = Calendar::with('doctor')->find($calendarId);
            if (!$calendar || !$calendar->doctor) {
                $validator->errors()->add('calendar_id', 'O calendário selecionado não possui um médico associado.');
                return;
            }

            $doctorId = $calendar->doctor_id;
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

            // Obter patient_id da sessão (para agendamentos públicos)
            $patientId = \Illuminate\Support\Facades\Session::get('public_patient_id');

            // Verificar se o paciente já possui um agendamento no mesmo dia com o mesmo médico
            if ($patientId && $doctorId) {
                // Verificar agendamentos normais
                $existingAppointmentSameDay = Appointment::where('patient_id', $patientId)
                    ->whereHas('calendar', function($query) use ($doctorId) {
                        $query->where('doctor_id', $doctorId);
                    })
                    ->whereDate('starts_at', $startsAt->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->first();

                if ($existingAppointmentSameDay) {
                    $validator->errors()->add('starts_at', 'Você já possui um agendamento neste dia com este médico. Um paciente não pode ter dois agendamentos no mesmo dia com o mesmo médico.');
                    return;
                }

                // Verificar agendamentos recorrentes ativos que já geraram agendamentos neste dia
                $recurringGeneratedAppointment = Appointment::where('patient_id', $patientId)
                    ->whereHas('calendar', function($query) use ($doctorId) {
                        $query->where('doctor_id', $doctorId);
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
                $activeRecurringAppointment = RecurringAppointment::where('patient_id', $patientId)
                    ->where('doctor_id', $doctorId)
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

