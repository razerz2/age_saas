<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Http\Requests\Tenant\StoreRecurringAppointmentRequest;
use App\Http\Requests\Tenant\UpdateRecurringAppointmentRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecurringAppointmentController extends Controller
{
    public function index()
    {
        $recurringAppointments = RecurringAppointment::with(['patient', 'doctor.user', 'appointmentType', 'rules'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('tenant.appointments.recurring.index', compact('recurringAppointments'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();
        
        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.appointments.recurring.create', compact(
            'doctors',
            'patients'
        ));
    }

    public function store(StoreRecurringAppointmentRequest $request)
    {
        try {
            $data = $request->validated();
            $rules = $data['rules'] ?? [];
            unset($data['rules']);

            // Validar que há pelo menos uma regra
            if (empty($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['rules' => 'É necessário pelo menos uma regra de recorrência.']);
            }

            // Filtrar regras vazias
            $rules = array_filter($rules, function($rule) {
                return !empty($rule['weekday']) && (!empty($rule['start_time']) || !empty($rule['time_slot']));
            });

            if (empty($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['rules' => 'É necessário pelo menos uma regra de recorrência válida.']);
            }

            // Remover specialty_id se existir (não existe na tabela)
            unset($data['specialty_id']);
            
            $data['id'] = Str::uuid();
            $data['active'] = true;
            
            $recurringAppointment = RecurringAppointment::create($data);

            // Criar regras
            foreach ($rules as $ruleData) {
                // Processar time_slot se existir (formato: "HH:mm|HH:mm")
                if (isset($ruleData['time_slot']) && !empty($ruleData['time_slot'])) {
                    [$startTime, $endTime] = explode('|', $ruleData['time_slot']);
                    $ruleData['start_time'] = $startTime;
                    $ruleData['end_time'] = $endTime;
                    unset($ruleData['time_slot']);
                }

                // Validar que start_time e end_time existem
                if (empty($ruleData['start_time']) || empty($ruleData['end_time'])) {
                    continue; // Pular regras sem horários válidos
                }

                // Validar que start_time e end_time são diferentes
                if ($ruleData['start_time'] === $ruleData['end_time']) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['rules' => 'A hora de início e fim não podem ser iguais.']);
                }

                $ruleData['id'] = Str::uuid();
                $ruleData['recurring_appointment_id'] = $recurringAppointment->id;
                if (!isset($ruleData['frequency'])) {
                    $ruleData['frequency'] = 'weekly';
                }
                if (!isset($ruleData['interval'])) {
                    $ruleData['interval'] = 1;
                }
                
                RecurringAppointmentRule::create($ruleData);
            }

            // Verificar se pelo menos uma regra foi criada
            if ($recurringAppointment->rules()->count() === 0) {
                $recurringAppointment->delete(); // Remover se não tiver regras
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['rules' => 'Nenhuma regra válida foi criada. Verifique os dados informados.']);
            }

            return redirect()->route('tenant.recurring-appointments.index')
                ->with('success', 'Agendamento recorrente criado com sucesso.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erros de validação são tratados automaticamente pelo Laravel
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao criar agendamento recorrente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['_token'])
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar agendamento recorrente. Por favor, verifique os dados e tente novamente.']);
        }
    }

    public function show($id)
    {
        $recurringAppointment = RecurringAppointment::with([
            'patient',
            'doctor.user',
            'appointmentType',
            'rules',
            'appointments' => function($query) {
                $query->orderBy('starts_at', 'desc');
            }
        ])->findOrFail($id);

        return view('tenant.appointments.recurring.show', compact('recurringAppointment'));
    }

    public function edit($id)
    {
        $recurringAppointment = RecurringAppointment::with('rules')->findOrFail($id);
        
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();
        
        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.appointments.recurring.edit', compact(
            'recurringAppointment',
            'doctors',
            'patients'
        ));
    }

    public function update(UpdateRecurringAppointmentRequest $request, $id)
    {
        $recurringAppointment = RecurringAppointment::findOrFail($id);
        
        $data = $request->validated();
        $rules = $data['rules'];
        unset($data['rules']);

        $recurringAppointment->update($data);

        // Remover regras antigas
        $recurringAppointment->rules()->delete();

        // Criar novas regras
        foreach ($rules as $ruleData) {
            // Processar time_slot se existir (formato: "HH:mm|HH:mm")
            if (isset($ruleData['time_slot']) && !empty($ruleData['time_slot'])) {
                [$startTime, $endTime] = explode('|', $ruleData['time_slot']);
                $ruleData['start_time'] = $startTime;
                $ruleData['end_time'] = $endTime;
                unset($ruleData['time_slot']);
            }

            // Validar que start_time e end_time são diferentes
            if (isset($ruleData['start_time']) && isset($ruleData['end_time']) && $ruleData['start_time'] === $ruleData['end_time']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['rules' => 'A hora de início e fim não podem ser iguais.']);
            }

            $ruleData['id'] = Str::uuid();
            $ruleData['recurring_appointment_id'] = $recurringAppointment->id;
            if (!isset($ruleData['frequency'])) {
                $ruleData['frequency'] = 'weekly';
            }
            if (!isset($ruleData['interval'])) {
                $ruleData['interval'] = 1;
            }
            RecurringAppointmentRule::create($ruleData);
        }

        return redirect()->route('tenant.recurring-appointments.index')
            ->with('success', 'Agendamento recorrente atualizado com sucesso.');
    }

    public function cancel($id)
    {
        $recurringAppointment = RecurringAppointment::findOrFail($id);
        
        return view('tenant.appointments.recurring.cancel', compact('recurringAppointment'));
    }

    public function destroy($id)
    {
        $recurringAppointment = RecurringAppointment::findOrFail($id);
        
        // Desativar ao invés de deletar
        $recurringAppointment->update(['active' => false]);

        return redirect()->route('tenant.recurring-appointments.index')
            ->with('success', 'Agendamento recorrente cancelado com sucesso.');
    }

    /**
     * API: Buscar business hours do médico
     */
    public function getBusinessHoursByDoctor($doctorId)
    {
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get()
            ->map(function($bh) {
                return [
                    'weekday' => $bh->weekday,
                    'weekday_name' => $this->getWeekdayName($bh->weekday),
                    'weekday_string' => $this->getWeekdayString($bh->weekday),
                    'start_time' => $bh->start_time,
                    'end_time' => $bh->end_time,
                ];
            })
            ->groupBy('weekday');

        // Organizar por dia da semana
        $result = [];
        foreach ($businessHours as $weekday => $hours) {
            $result[] = [
                'weekday' => $weekday,
                'weekday_name' => $hours->first()['weekday_name'],
                'weekday_string' => $hours->first()['weekday_string'],
                'hours' => $hours->map(function($h) {
                    return [
                        'start_time' => $h['start_time'],
                        'end_time' => $h['end_time'],
                    ];
                })->values()->all(),
            ];
        }

        return response()->json($result);
    }

    /**
     * Converte número do dia da semana para nome
     */
    private function getWeekdayName($weekday)
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];
        return $days[$weekday] ?? '';
    }

    /**
     * Converte número do dia da semana para string (monday, tuesday, etc)
     */
    private function getWeekdayString($weekday)
    {
        $days = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];
        return $days[$weekday] ?? 'monday';
    }

    /**
     * API: Buscar horários disponíveis para recorrência
     * Considera: business hours, duração do tipo de consulta, conflitos com agendamentos existentes
     */
    public function getAvailableSlotsForRecurring(Request $request, $doctorId)
    {
        $request->validate([
            'weekday' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'appointment_type_id' => 'required|exists:tenant.appointment_types,id',
            'start_date' => 'nullable|date',
            'recurring_appointment_id' => 'nullable|exists:tenant.recurring_appointments,id', // Para edição, excluir a própria recorrência
        ]);

        $weekdayString = $request->weekday;
        $weekdayNumber = RecurringAppointmentRule::weekdayToNumber($weekdayString);
        $appointmentTypeId = $request->appointment_type_id;
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $recurringAppointmentId = $request->recurring_appointment_id;

        // Buscar tipo de consulta para obter duração
        $appointmentType = AppointmentType::find($appointmentTypeId);
        if (!$appointmentType) {
            return response()->json([]);
        }

        $duration = $appointmentType->duration_min ?? 30; // Padrão 30 minutos

        // Buscar horários comerciais do médico para o dia da semana
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekdayNumber)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return response()->json([]);
        }

        // Buscar calendários do médico
        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');

        // Buscar agendamentos recorrentes ativos que já ocupam este dia da semana
        $existingRecurringRules = RecurringAppointmentRule::whereHas('recurringAppointment', function($query) use ($doctorId, $startDate, $recurringAppointmentId) {
            $query->where('doctor_id', $doctorId)
                  ->where('active', true)
                  ->where('start_date', '<=', $startDate->format('Y-m-d'))
                  ->where(function($q) use ($startDate) {
                      $q->where('end_type', 'none')
                        ->orWhere(function($sq) use ($startDate) {
                            $sq->where('end_type', 'date')
                               ->where('end_date', '>=', $startDate->format('Y-m-d'));
                        })
                        ->orWhere('end_type', 'total_sessions');
                  });
            
            // Excluir a própria recorrência em caso de edição
            if ($recurringAppointmentId) {
                $query->where('id', '!=', $recurringAppointmentId);
            }
        })
        ->where('weekday', $weekdayString)
        ->get();

        // Buscar agendamentos normais existentes no mesmo dia da semana
        // Vamos verificar as próximas 12 semanas a partir da data inicial
        $existingAppointments = collect();
        
        // Só buscar agendamentos se houver calendários
        if ($calendars->isNotEmpty()) {
            for ($i = 0; $i < 12; $i++) {
                $checkDate = $startDate->copy()->addWeeks($i);
                // Ajustar para o dia da semana correto
                while ($checkDate->dayOfWeek != $weekdayNumber) {
                    $checkDate->addDay();
                }
                
                $dayAppointments = Appointment::whereIn('calendar_id', $calendars)
                    ->whereDate('starts_at', $checkDate->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->get();
                
                $existingAppointments = $existingAppointments->merge($dayAppointments);
            }
        }

        $availableSlots = [];

        foreach ($businessHours as $businessHour) {
            try {
                // Converter horários para Carbon (formato pode ser 'H:i:s' ou 'H:i')
                $startTimeStr = $businessHour->start_time;
                $endTimeStr = $businessHour->end_time;
                
                // Se já estiver no formato correto, usar diretamente, senão parsear
                $startTime = strlen($startTimeStr) <= 5 
                    ? Carbon::createFromFormat('H:i', $startTimeStr)
                    : Carbon::parse($startTimeStr);
                    
                $endTime = strlen($endTimeStr) <= 5 
                    ? Carbon::createFromFormat('H:i', $endTimeStr)
                    : Carbon::parse($endTimeStr);

                $currentSlot = $startTime->copy();

                while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                    $slotStartTime = $currentSlot->format('H:i');
                    $slotEndTime = $currentSlot->copy()->addMinutes($duration)->format('H:i');

                    // Verificar se o slot está bloqueado por uma regra de recorrência existente
                    $hasRecurringConflict = $existingRecurringRules->filter(function($rule) use ($slotStartTime, $slotEndTime) {
                        // Verifica se a regra bloqueia o slot:
                        // - A regra começa antes ou no início do slot
                        // - A regra termina depois ou no fim do slot
                        return $rule->start_time <= $slotStartTime && $rule->end_time >= $slotEndTime;
                    })->isNotEmpty();

                    // Verificar se o slot conflita com agendamentos normais existentes
                    $hasAppointmentConflict = false;
                    if (!$hasRecurringConflict) {
                        $hasAppointmentConflict = $existingAppointments->filter(function($appointment) use ($slotStartTime, $slotEndTime) {
                            $apptStart = Carbon::parse($appointment->starts_at)->format('H:i');
                            $apptEnd = Carbon::parse($appointment->ends_at)->format('H:i');
                            
                            // Verifica sobreposição
                            return ($slotStartTime < $apptEnd && $slotEndTime > $apptStart);
                        })->isNotEmpty();
                    }

                    if (!$hasRecurringConflict && !$hasAppointmentConflict) {
                        $availableSlots[] = [
                            'start' => $slotStartTime,
                            'end' => $slotEndTime,
                            'display' => $slotStartTime . ' - ' . $slotEndTime,
                        ];
                    }

                    $currentSlot->addMinutes($duration);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao processar business hour', [
                    'business_hour_id' => $businessHour->id,
                    'start_time' => $businessHour->start_time,
                    'end_time' => $businessHour->end_time,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return response()->json($availableSlots);
    }
}

