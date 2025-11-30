<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use Carbon\Carbon;

class StoreRecurringAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => ['required', 'exists:tenant.patients,id'],
            'doctor_id' => ['required', 'exists:tenant.doctors,id'],
            'appointment_type_id' => ['required', 'exists:tenant.appointment_types,id'],
            'start_date' => ['required', 'date'],
            'end_type' => ['required', 'in:none,date'],
            'end_date' => ['nullable', 'date', 'after:start_date', 'required_if:end_type,date'],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.weekday' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'rules.*.time_slot' => ['nullable', 'string'],
            'rules.*.start_time' => ['required', 'date_format:H:i'],
            'rules.*.end_time' => ['required', 'date_format:H:i'],
            'rules.*.frequency' => ['nullable', 'in:weekly,biweekly,monthly'],
            'rules.*.interval' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'patient_id.required' => 'O paciente é obrigatório.',
            'doctor_id.required' => 'O médico é obrigatório.',
            'appointment_type_id.required' => 'O tipo de consulta é obrigatório.',
            'start_date.required' => 'A data inicial é obrigatória.',
            'end_type.required' => 'O tipo de término é obrigatório.',
            'end_date.required_if' => 'A data final é obrigatória quando o tipo de término é "Data final".',
            'end_date.after' => 'A data final deve ser posterior à data inicial.',
            'rules.required' => 'É necessário pelo menos uma regra de recorrência.',
            'rules.min' => 'É necessário pelo menos uma regra de recorrência.',
            'rules.*.weekday.required' => 'O dia da semana é obrigatório em todas as regras.',
            'rules.*.start_time.required' => 'A hora de início é obrigatória em todas as regras.',
            'rules.*.end_time.required' => 'A hora de término é obrigatória em todas as regras.',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rules = $this->input('rules', []);
            
            // Validar cada regra individualmente
            foreach ($rules as $index => $rule) {
                // Verificar se start_time e end_time existem e são diferentes
                if (isset($rule['start_time']) && isset($rule['end_time'])) {
                    if ($rule['start_time'] === $rule['end_time']) {
                        $validator->errors()->add(
                            "rules.{$index}.end_time",
                            'A hora de término deve ser diferente da hora de início.'
                        );
                    }
                    
                    // Verificar se end_time é depois de start_time
                    try {
                        $startTime = \Carbon\Carbon::createFromFormat('H:i', $rule['start_time']);
                        $endTime = \Carbon\Carbon::createFromFormat('H:i', $rule['end_time']);
                        
                        if ($endTime->lte($startTime)) {
                            $validator->errors()->add(
                                "rules.{$index}.end_time",
                                'A hora de término deve ser posterior à hora de início.'
                            );
                        }
                    } catch (\Exception $e) {
                        // Ignora erros de parsing, já será validado pelo date_format
                    }
                }
            }

            // Verificar se já existe agendamento normal ou recorrente no mesmo dia da semana com mesmo paciente e médico
            if ($this->patient_id && $this->doctor_id && $this->start_date) {
                $startDate = Carbon::parse($this->start_date);
                $weekdays = array_column($rules, 'weekday');
                
                // Verificar agendamentos normais que caem nos dias da semana das regras
                foreach ($weekdays as $weekday) {
                    $weekdayNumber = RecurringAppointmentRule::weekdayToNumber($weekday);
                    
                    // Verificar se existe agendamento normal no próximo dia da semana após a data inicial
                    $nextDate = $startDate->copy();
                    while ($nextDate->dayOfWeek !== $weekdayNumber) {
                        $nextDate->addDay();
                    }
                    
                    // Verificar agendamentos normais neste dia
                    $existingAppointment = Appointment::where('patient_id', $this->patient_id)
                        ->whereHas('calendar', function($query) {
                            $query->where('doctor_id', $this->doctor_id);
                        })
                        ->whereDate('starts_at', $nextDate->format('Y-m-d'))
                        ->whereIn('status', ['scheduled', 'rescheduled'])
                        ->first();

                    if ($existingAppointment) {
                        $validator->errors()->add(
                            'start_date',
                            'Já existe um agendamento normal no dia ' . $nextDate->format('d/m/Y') . ' (dia da semana: ' . ucfirst($weekday) . ') com este paciente e médico. Um paciente não pode ter dois agendamentos no mesmo dia com o mesmo médico.'
                        );
                        return;
                    }
                }

                // Verificar se já existe outro agendamento recorrente ativo com mesmo paciente e médico
                // que tem regras para os mesmos dias da semana
                $existingRecurringAppointment = RecurringAppointment::where('patient_id', $this->patient_id)
                    ->where('doctor_id', $this->doctor_id)
                    ->where('active', true)
                    ->where(function($query) use ($startDate) {
                        $query->where('end_type', 'none')
                            ->orWhere(function($q) use ($startDate) {
                                $q->where('end_type', 'date')
                                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
                            });
                    })
                    ->whereHas('rules', function($query) use ($weekdays) {
                        $query->whereIn('weekday', $weekdays);
                    })
                    ->first();

                if ($existingRecurringAppointment) {
                    $validator->errors()->add(
                        'start_date',
                        'Já existe um agendamento recorrente ativo com este paciente e médico que gera agendamentos nos mesmos dias da semana. Um paciente não pode ter dois agendamentos recorrentes no mesmo dia da semana com o mesmo médico.'
                    );
                    return;
                }
            }
        });
    }
}

