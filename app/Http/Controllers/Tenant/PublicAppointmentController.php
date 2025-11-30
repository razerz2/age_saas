<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePublicAppointmentRequest;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\AppointmentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PublicAppointmentController extends Controller
{
    /**
     * Exibe o formulário de criação de agendamento público
     */
    public function create(Request $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        // Verifica se o paciente foi identificado
        $patientId = Session::get('public_patient_id');
        if (!$patientId) {
            return redirect()->route('public.patient.identify', ['tenant' => $tenant])
                ->with('error', 'Por favor, identifique-se primeiro para realizar o agendamento.');
        }

        // Busca os médicos ativos
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('id')
            ->get();

        // Busca o nome do paciente da sessão ou do banco
        $patientName = Session::get('public_patient_name');
        if (!$patientName) {
            $patient = \App\Models\Tenant\Patient::find($patientId);
            $patientName = $patient ? $patient->full_name : 'Paciente';
        }

        return view('tenant.public.appointment-create', [
            'tenant' => $tenantModel,
            'doctors' => $doctors,
            'patientId' => $patientId,
            'patientName' => $patientName
        ]);
    }

    /**
     * Armazena o agendamento público
     */
    public function store(StorePublicAppointmentRequest $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        // Verifica se o paciente foi identificado
        $patientId = Session::get('public_patient_id');
        if (!$patientId) {
            return redirect()->route('public.patient.identify', ['tenant' => $tenant])
                ->with('error', 'Por favor, identifique-se primeiro para realizar o agendamento.');
        }

        $data = $request->validated();
        
        // Validação adicional: garante que o calendário existe e é válido
        $calendar = Calendar::find($data['calendar_id']);
        if (!$calendar) {
            return back()
                ->withErrors(['calendar_id' => 'Calendário não encontrado.'])
                ->withInput();
        }
        
        $data['id'] = Str::uuid();
        $data['patient_id'] = $patientId; // Usa o paciente da sessão
        $data['status'] = 'scheduled'; // Status padrão para agendamentos públicos

        // Aplicar lógica de appointment_mode baseado na configuração
        $mode = \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            $data['appointment_mode'] = 'presencial';
        } elseif ($mode === 'online') {
            $data['appointment_mode'] = 'online';
        } else { // user_choice
            $data['appointment_mode'] = $request->appointment_mode ?? 'presencial';
        }

        $appointment = Appointment::create($data);

        // Salva o ID do agendamento na sessão para permitir visualização
        Session::put('last_appointment_id', $appointment->id);
        Session::put('last_appointment_patient_id', $patientId);

        // Limpa a sessão do paciente após criar o agendamento
        Session::forget('public_patient_id');
        Session::forget('public_patient_name');

        // Redireciona para a página de detalhes do agendamento
        return redirect()->route('public.appointment.show', [
            'tenant' => $tenant,
            'appointment_id' => $appointment->id
        ])->with('success', 'Agendamento realizado com sucesso!');
    }

    /**
     * Página de sucesso após criar agendamento
     */
    public function success(Request $request, $tenant, $appointmentId = null)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Usa o appointment_id da URL ou da sessão
        $appointmentId = $appointmentId ?? Session::get('last_appointment_id');

        return view('tenant.public.appointment-success', [
            'tenant' => $tenantModel,
            'appointment_id' => $appointmentId
        ]);
    }

    /**
     * Exibe detalhes do agendamento público (somente leitura)
     */
    public function show(Request $request, $tenant, $appointmentId)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        // Busca o agendamento
        $appointment = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->findOrFail($appointmentId);

        // Verifica se o paciente tem permissão para ver este agendamento
        // O paciente só pode ver seus próprios agendamentos
        $patientIdFromSession = Session::get('last_appointment_patient_id') ?? Session::get('public_patient_id');
        
        // Valida que o agendamento pertence ao paciente da sessão
        // Se não houver sessão ou não corresponder, bloqueia o acesso
        if (!$patientIdFromSession || $appointment->patient_id !== $patientIdFromSession) {
            abort(403, 'Você não tem permissão para visualizar este agendamento. Por favor, identifique-se novamente.');
        }

        return view('tenant.public.appointment-show', [
            'tenant' => $tenantModel,
            'appointment' => $appointment
        ]);
    }

    /**
     * API Pública: Buscar calendários por médico
     */
    public function getCalendarsByDoctor($tenant, $doctorId)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            return response()->json([], 404);
        }

        $tenantModel->makeCurrent();

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
     * API Pública: Buscar tipos de consulta por médico
     */
    public function getAppointmentTypesByDoctor($tenant, $doctorId)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            return response()->json([], 404);
        }

        $tenantModel->makeCurrent();

        try {
            $columns = \DB::connection('tenant')
                ->select("SELECT column_name FROM information_schema.columns WHERE table_name = 'appointment_types' AND column_name = 'doctor_id'");
            
            if (empty($columns)) {
                $types = AppointmentType::where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } else {
                $types = AppointmentType::where(function($query) use ($doctorId) {
                        $query->where('doctor_id', $doctorId)
                              ->orWhereNull('doctor_id');
                    })
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            }

            return response()->json($types->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'duration_min' => $type->duration_min,
                ];
            }));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * API Pública: Buscar especialidades por médico
     */
    public function getSpecialtiesByDoctor($tenant, $doctorId)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            return response()->json([], 404);
        }

        $tenantModel->makeCurrent();

        try {
            $doctor = Doctor::findOrFail($doctorId);
            $specialties = $doctor->specialties()->orderBy('name')->get();

            return response()->json($specialties->map(function($specialty) {
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                ];
            }));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * API Pública: Buscar horários disponíveis
     */
    public function getAvailableSlots(Request $request, $tenant, $doctorId)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            return response()->json([], 404);
        }

        $tenantModel->makeCurrent();

        $request->validate([
            'date' => 'required|date',
            'appointment_type_id' => 'nullable|exists:tenant.appointment_types,id',
        ]);

        $date = Carbon::parse($request->date);
        $weekday = $date->dayOfWeek;
        
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->orderBy('start_time')
            ->get();

        if ($businessHours->isEmpty()) {
            return response()->json([]);
        }

        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');
        
        $existingAppointments = \App\Models\Tenant\Appointment::whereIn('calendar_id', $calendars)
            ->whereDate('starts_at', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        $duration = 30;
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

                $hasConflict = $existingAppointments->filter(function($appointment) use ($slotStart, $slotEnd) {
                    $apptStart = Carbon::parse($appointment->starts_at);
                    $apptEnd = Carbon::parse($appointment->ends_at);
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

