<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Patient;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\BusinessHour;
use App\Http\Requests\Tenant\StoreAppointmentRequest;
use App\Http\Requests\Tenant\UpdateAppointmentRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->orderBy('starts_at')
            ->paginate(30);

        return view('tenant.appointments.index', compact('appointments'));
    }

    public function create()
    {
        // Listar médicos ativos (com status active)
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();
        
        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.appointments.create', compact(
            'doctors',
            'patients'
        ));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Appointment::create($data);

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->load(['calendar.doctor.user', 'patient', 'type', 'specialty']);
        return view('tenant.appointments.show', compact('appointment'));
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $calendars      = Calendar::with('doctor.user')->orderBy('name')->get();
        $patients       = Patient::orderBy('full_name')->get();
        $specialties    = MedicalSpecialty::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        $appointment->load(['calendar', 'patient', 'specialty', 'type']);

        return view('tenant.appointments.edit', compact(
            'appointment',
            'calendars',
            'patients',
            'specialties',
            'appointmentTypes'
        ));
    }

    public function update(UpdateAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->validated());

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento removido.');
    }

    public function events(Request $request, $id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor.user');
        
        // Se for uma requisição AJAX, esperando JSON, ou se tiver parâmetros de query do FullCalendar (start/end), retorna JSON
        $isFullCalendarRequest = $request->has('start') || $request->has('end') || 
                                 $request->ajax() || $request->wantsJson() || $request->expectsJson() ||
                                 str_contains($request->header('Accept', ''), 'application/json');
        
        if ($isFullCalendarRequest) {
            $user = Auth::guard('tenant')->user();
            
            // Query base para buscar os agendamentos do calendário
            $query = Appointment::where('calendar_id', $calendar->id)
                ->with(['patient', 'type', 'specialty']);
            
            // Verifica permissão para visualizar os eventos
            if ($user && $user->is_doctor && $user->doctor) {
                // Médico só pode ver eventos do seu próprio calendário
                if ($calendar->doctor_id !== $user->doctor->id) {
                    return response()->json([]);
                }
            } elseif ($user && !$user->is_doctor) {
                // Usuário não médico precisa ter permissão para ver o médico
                if (!$user->canViewAllDoctors() && !$user->canViewDoctor($calendar->doctor_id)) {
                    return response()->json([]);
                }
            }
            
            $appointments = $query->get();
            
            // Formata os eventos para o FullCalendar
            $events = $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient ? $appointment->patient->full_name : 'Sem paciente',
                    'start' => $appointment->starts_at->toIso8601String(),
                    'end' => $appointment->ends_at ? $appointment->ends_at->toIso8601String() : null,
                    'backgroundColor' => $this->getEventColor($appointment->status ?? 'scheduled'),
                    'borderColor' => $this->getEventColor($appointment->status ?? 'scheduled'),
                    'extendedProps' => [
                        'patient' => $appointment->patient ? $appointment->patient->full_name : null,
                        'type' => $appointment->type ? $appointment->type->name : null,
                        'specialty' => $appointment->specialty ? $appointment->specialty->name : null,
                        'status' => $appointment->status ?? 'scheduled',
                        'notes' => $appointment->notes ?? null,
                    ]
                ];
            });
            
            return response()->json($events);
        }
        
        // Retorna a view para acesso direto - mas primeiro verifica permissões
        $user = Auth::guard('tenant')->user();
        
        // Verifica permissão para visualizar o calendário
        if ($user && $user->is_doctor && $user->doctor) {
            // Médico só pode ver seu próprio calendário
            if ($calendar->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para visualizar este calendário.');
            }
        } elseif ($user && !$user->is_doctor) {
            // Usuário não médico precisa ter permissão para ver o médico
            if (!$user->canViewAllDoctors() && !$user->canViewDoctor($calendar->doctor_id)) {
                abort(403, 'Você não tem permissão para visualizar este calendário.');
            }
        } else {
            // Usuário não autenticado
            abort(403, 'Você precisa estar autenticado para visualizar este calendário.');
        }
        
        return view('tenant.calendars.events', compact('calendar'));
    }
    
    /**
     * Retorna a cor do evento baseado no status
     */
    private function getEventColor($status)
    {
        return match($status) {
            'completed' => '#28a745',
            'cancelled' => '#dc3545',
            'confirmed' => '#007bff',
            'pending' => '#ffc107',
            default => '#6c757d',
        };
    }

    /**
     * API: Buscar calendários por médico
     */
    public function getCalendarsByDoctor($doctorId)
    {
        $calendars = Calendar::where('doctor_id', $doctorId)
            ->orderBy('name')
            ->get()
            ->map(function($calendar) {
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                ];
            });

        return response()->json($calendars);
    }

    /**
     * API: Buscar tipos de consulta por médico
     */
    public function getAppointmentTypesByDoctor($doctorId)
    {
        try {
            // Verificar se a coluna doctor_id existe
            $columns = \DB::connection('tenant')
                ->select("SELECT column_name FROM information_schema.columns WHERE table_name = 'appointment_types' AND column_name = 'doctor_id'");
            
            if (empty($columns)) {
                // Se a coluna não existe, retornar todos os tipos ativos (compatibilidade temporária)
                $types = AppointmentType::where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'duration_min' => $type->duration_min,
                        ];
                    });
            } else {
                // Se a coluna existe, filtrar por médico
                // Retorna tipos do médico OU tipos sem médico atribuído (doctor_id IS NULL)
                $types = AppointmentType::where(function($query) use ($doctorId) {
                        $query->where('doctor_id', $doctorId)
                              ->orWhereNull('doctor_id');
                    })
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'duration_min' => $type->duration_min,
                        ];
                    });
            }

            return response()->json($types);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar tipos de consulta por médico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar especialidades por médico
     */
    public function getSpecialtiesByDoctor($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            
            \Log::info('Buscando especialidades para médico', [
                'doctor_id' => $doctorId,
                'doctor_name' => $doctor->user->name_full ?? $doctor->user->name ?? 'N/A'
            ]);
            
            $specialties = $doctor->specialties()
                ->orderBy('name')
                ->get();
            
            \Log::info('Especialidades encontradas', [
                'doctor_id' => $doctorId,
                'count' => $specialties->count(),
                'specialties' => $specialties->pluck('name')->toArray()
            ]);
            
            $result = $specialties->map(function($specialty) {
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar especialidades por médico', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar array vazio em caso de erro
            return response()->json([]);
        }
    }

    /**
     * API: Buscar horários disponíveis para um médico em uma data específica
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $request->validate([
            'date' => 'required|date',
            'appointment_type_id' => 'nullable|exists:tenant.appointment_types,id',
        ]);

        $date = Carbon::parse($request->date);
        $weekday = $date->dayOfWeek; // 0 = Domingo, 6 = Sábado
        
        // Buscar horários comerciais do médico para o dia da semana
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return response()->json([]);
        }

        // Buscar calendários do médico
        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');
        
        // Buscar agendamentos existentes para o dia (apenas scheduled e rescheduled)
        // Cancelados e não comparecidos não ocupam horário
        $existingAppointments = Appointment::whereIn('calendar_id', $calendars)
            ->whereDate('starts_at', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        // Duração padrão (30 minutos) ou do tipo de consulta
        $duration = 30; // minutos padrão
        if ($request->appointment_type_id) {
            $appointmentType = AppointmentType::find($request->appointment_type_id);
            if ($appointmentType) {
                $duration = $appointmentType->duration_min;
            }
        }

        $availableSlots = [];

        foreach ($businessHours as $businessHour) {
            $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->start_time);
            $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->end_time);

            $currentSlot = $startTime->copy();

            while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                $slotStart = $currentSlot->copy();
                $slotEnd = $currentSlot->copy()->addMinutes($duration);

                // Verificar se o slot não conflita com agendamentos existentes
                // Considera apenas agendamentos com status 'scheduled' ou 'rescheduled'
                $hasConflict = $existingAppointments->filter(function($appointment) use ($slotStart, $slotEnd) {
                    $apptStart = Carbon::parse($appointment->starts_at);
                    $apptEnd = Carbon::parse($appointment->ends_at);
                    
                    // Verifica sobreposição: 
                    // - Slot começa antes do agendamento terminar
                    // - Slot termina depois do agendamento começar
                    // Isso cobre todos os casos: sobreposição parcial, slot dentro do agendamento, agendamento dentro do slot
                    return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                })->isNotEmpty();

                if (!$hasConflict) {
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime_start' => $slotStart->toIso8601String(),
                        'datetime_end' => $slotEnd->toIso8601String(),
                    ];
                }

                $currentSlot->addMinutes($duration);
            }
        }

        return response()->json($availableSlots);
    }
}
