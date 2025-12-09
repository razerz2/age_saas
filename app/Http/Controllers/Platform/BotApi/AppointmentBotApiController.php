<?php

namespace App\Http\Controllers\Platform\BotApi;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentBotApiController extends Controller
{
    /**
     * Criar agendamento
     */
    public function create(Request $request)
    {
        $tenant = $request->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        // Validar dados
        $validator = Validator::make($request->all(), [
            'patient_phone' => 'required|string',
            'patient_name' => 'required|string|max:255',
            'doctor_id' => 'required|uuid|exists:tenant.doctors,id',
            'appointment_type_id' => 'nullable|uuid|exists:tenant.appointment_types,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $tenant->makeCurrent();
            
            return (function () use ($validated, $tenant) {
                // Buscar ou criar paciente
                $phone = preg_replace('/[^0-9]/', '', $validated['patient_phone']);
                $patient = Patient::where('phone', $phone)->first();

                if (!$patient) {
                    $patient = Patient::create([
                        'id' => Str::uuid(),
                        'full_name' => $validated['patient_name'],
                        'phone' => $phone,
                        'is_active' => true,
                    ]);
                }

                // Buscar médico e calendário
                $doctor = Doctor::find($validated['doctor_id']);
                if (!$doctor || !$doctor->user || $doctor->user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Médico não encontrado ou inativo'
                    ], 404);
                }

                // Buscar calendário padrão do médico
                $calendar = Calendar::where('doctor_id', $doctor->id)->first();
                if (!$calendar) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Calendário não encontrado para este médico'
                    ], 404);
                }

                // Validar tipo de consulta
                $appointmentType = null;
                if ($validated['appointment_type_id']) {
                    $appointmentType = AppointmentType::find($validated['appointment_type_id']);
                    if (!$appointmentType || !$appointmentType->is_active) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Tipo de consulta não encontrado ou inativo'
                        ], 404);
                    }
                }

                // Montar data/hora
                $dateTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
                $endsAt = $dateTime->copy();

                // Calcular duração
                $duration = 30; // padrão
                if ($appointmentType) {
                    $duration = $appointmentType->duration_min ?? 30;
                }
                $endsAt->addMinutes($duration);

                // Validar horário disponível
                $validationError = $this->validateAvailableSlot($doctor->id, $dateTime, $endsAt);
                if ($validationError) {
                    return response()->json([
                        'success' => false,
                        'error' => $validationError
                    ], 422);
                }

                // Verificar conflitos
                $conflict = Appointment::where('calendar_id', $calendar->id)
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->where(function($query) use ($dateTime, $endsAt) {
                        $query->where('starts_at', '<', $endsAt)
                              ->where('ends_at', '>', $dateTime);
                    })
                    ->first();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Horário já está ocupado'
                    ], 422);
                }

                // Criar agendamento
                $appointment = Appointment::create([
                    'id' => Str::uuid(),
                    'calendar_id' => $calendar->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_type' => $appointmentType?->id,
                    'starts_at' => $dateTime,
                    'ends_at' => $endsAt,
                    'status' => 'scheduled',
                    'notes' => $validated['notes'] ?? null,
                    'appointment_mode' => 'presencial', // padrão
                ]);

                Log::info('Bot API - Agendamento criado', [
                    'tenant_id' => $tenant->id,
                    'appointment_id' => $appointment->id,
                    'patient_id' => $patient->id,
                ]);

                return response()->json([
                    'success' => true,
                    'appointment_id' => $appointment->id,
                    'message' => 'Agendamento criado com sucesso.'
                ]);
            })();
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao criar agendamento', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remarcar agendamento
     */
    public function reschedule(Request $request)
    {
        $tenant = $request->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|uuid',
            'new_date' => 'required|date',
            'new_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $tenant->makeCurrent();
            
            return (function () use ($validated, $tenant) {
                $appointment = Appointment::find($validated['appointment_id']);
                
                if (!$appointment) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Agendamento não encontrado'
                    ], 404);
                }

                if (in_array($appointment->status, ['canceled', 'attended', 'no_show'])) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Não é possível remarcar um agendamento ' . $appointment->status
                    ], 422);
                }

                $newDateTime = Carbon::parse($validated['new_date'] . ' ' . $validated['new_time']);
                
                if ($newDateTime->lt(now())) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Não é possível remarcar para uma data/hora passada'
                    ], 422);
                }

                // Calcular nova data de término
                $duration = $appointment->starts_at->diffInMinutes($appointment->ends_at);
                $newEndsAt = $newDateTime->copy()->addMinutes($duration);

                // Validar horário disponível
                $validationError = $this->validateAvailableSlot($appointment->doctor_id, $newDateTime, $newEndsAt);
                if ($validationError) {
                    return response()->json([
                        'success' => false,
                        'error' => $validationError
                    ], 422);
                }

                // Verificar conflitos (exceto o próprio agendamento)
                $conflict = Appointment::where('calendar_id', $appointment->calendar_id)
                    ->where('id', '!=', $appointment->id)
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->where(function($query) use ($newDateTime, $newEndsAt) {
                        $query->where('starts_at', '<', $newEndsAt)
                              ->where('ends_at', '>', $newDateTime);
                    })
                    ->first();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Horário já está ocupado'
                    ], 422);
                }

                // Atualizar agendamento
                $appointment->update([
                    'starts_at' => $newDateTime,
                    'ends_at' => $newEndsAt,
                    'status' => 'rescheduled',
                ]);

                Log::info('Bot API - Agendamento remarcado', [
                    'tenant_id' => $tenant->id,
                    'appointment_id' => $appointment->id,
                ]);

                return response()->json([
                    'success' => true,
                    'appointment_id' => $appointment->id,
                    'message' => 'Agendamento remarcado com sucesso.'
                ]);
            })();
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao remarcar agendamento', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao remarcar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar agendamento
     */
    public function cancel(Request $request)
    {
        $tenant = $request->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|uuid',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $tenant->makeCurrent();
            
            return (function () use ($validated, $tenant) {
                $appointment = Appointment::find($validated['appointment_id']);
                
                if (!$appointment) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Agendamento não encontrado'
                    ], 404);
                }

                if ($appointment->status === 'canceled') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Agendamento já está cancelado'
                    ], 422);
                }

                $appointment->update([
                    'status' => 'canceled',
                    'notes' => ($appointment->notes ? $appointment->notes . "\n" : '') . 
                               'Cancelado via Bot API. Motivo: ' . ($validated['reason'] ?? 'Não informado'),
                ]);

                Log::info('Bot API - Agendamento cancelado', [
                    'tenant_id' => $tenant->id,
                    'appointment_id' => $appointment->id,
                ]);

                return response()->json([
                    'success' => true,
                    'appointment_id' => $appointment->id,
                    'message' => 'Agendamento cancelado com sucesso.'
                ]);
            })();
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao cancelar agendamento', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao cancelar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar agendamentos por telefone
     */
    public function byPhone($phone)
    {
        $tenant = request()->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        try {
            $tenant->makeCurrent();
            
            return (function () use ($phone, $tenant) {
                $phoneClean = preg_replace('/[^0-9]/', '', $phone);
                $patient = Patient::where('phone', $phoneClean)->first();

                if (!$patient) {
                    return response()->json([
                        'success' => true,
                        'appointments' => []
                    ]);
                }

                $appointments = Appointment::with(['doctor.user', 'type', 'specialty'])
                    ->where('patient_id', $patient->id)
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at')
                    ->get()
                    ->map(function($appointment) {
                        return [
                            'id' => $appointment->id,
                            'doctor_name' => $appointment->doctor->user->name_full ?? $appointment->doctor->user->name ?? 'N/A',
                            'type' => $appointment->type->name ?? 'Não especificado',
                            'date' => $appointment->starts_at->format('Y-m-d'),
                            'time' => $appointment->starts_at->format('H:i'),
                            'status' => $appointment->status,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'appointments' => $appointments
                ]);
            })();
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao listar agendamentos', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao listar agendamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida se o horário está disponível
     */
    private function validateAvailableSlot($doctorId, Carbon $startsAt, Carbon $endsAt)
    {
        $weekday = $startsAt->dayOfWeek;
        
        // Verificar se o médico atende no dia
        $businessHours = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->get();

        if ($businessHours->isEmpty()) {
            $weekdayNames = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
            return 'O médico não realiza atendimento em ' . $weekdayNames[$weekday];
        }

        // Verificar se está dentro do horário de atendimento
        $startTime = $startsAt->format('H:i:s');
        $endTime = $endsAt->format('H:i:s');
        $isWithinBusinessHours = false;

        foreach ($businessHours as $businessHour) {
            $bhStart = Carbon::parse($businessHour->start_time)->format('H:i:s');
            $bhEnd = Carbon::parse($businessHour->end_time)->format('H:i:s');

            if ($startTime >= $bhStart && $endTime <= $bhEnd) {
                // Verificar se não está dentro de um intervalo
                $isInBreak = false;
                if ($businessHour->break_start_time && $businessHour->break_end_time) {
                    $breakStart = Carbon::parse($businessHour->break_start_time)->format('H:i:s');
                    $breakEnd = Carbon::parse($businessHour->break_end_time)->format('H:i:s');
                    $isInBreak = ($startTime < $breakEnd && $endTime > $breakStart);
                }

                if (!$isInBreak) {
                    $isWithinBusinessHours = true;
                    break;
                }
            }
        }

        if (!$isWithinBusinessHours) {
            return 'O horário selecionado está fora do horário de atendimento do médico';
        }

        return null; // Sem erros
    }
}
