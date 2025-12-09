<?php

namespace App\Http\Controllers\Platform\BotApi;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AvailabilityBotApiController extends Controller
{
    /**
     * Listar horários disponíveis
     */
    public function slots(Request $request)
    {
        $tenant = $request->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|uuid|exists:tenant.doctors,id',
            'date' => 'required|date',
            'appointment_type_id' => 'nullable|uuid|exists:tenant.appointment_types,id',
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
                $date = Carbon::parse($validated['date']);
                $weekday = $date->dayOfWeek;
                
                $businessHours = BusinessHour::where('doctor_id', $validated['doctor_id'])
                    ->where('weekday', $weekday)
                    ->orderBy('start_time')
                    ->get();

                if ($businessHours->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'slots' => []
                    ]);
                }

                $calendars = Calendar::where('doctor_id', $validated['doctor_id'])->pluck('id');
                
                $existingAppointments = Appointment::whereIn('calendar_id', $calendars)
                    ->whereDate('starts_at', $date->format('Y-m-d'))
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->get();

                $duration = 30; // padrão
                if ($validated['appointment_type_id']) {
                    $appointmentType = AppointmentType::find($validated['appointment_type_id']);
                    if ($appointmentType) {
                        $duration = $appointmentType->duration_min ?? 30;
                    }
                }

                $availableSlots = [];

                foreach ($businessHours as $businessHour) {
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->start_time);
                    $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->end_time);
                    
                    // Verificar intervalo
                    $breakStartTime = null;
                    $breakEndTime = null;
                    if ($businessHour->break_start_time && $businessHour->break_end_time) {
                        $breakStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->break_start_time);
                        $breakEndTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHour->break_end_time);
                    }

                    $currentSlot = $startTime->copy();

                    while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
                        $slotStart = $currentSlot->copy();
                        $slotEnd = $currentSlot->copy()->addMinutes($duration);
                        
                        // Verificar se está no intervalo
                        $isInBreak = false;
                        if ($breakStartTime && $breakEndTime) {
                            $isInBreak = ($slotStart->lt($breakEndTime) && $slotEnd->gt($breakStartTime));
                        }
                        
                        if ($isInBreak) {
                            $currentSlot->addMinutes($duration);
                            continue;
                        }

                        // Verificar conflitos
                        $hasConflict = $existingAppointments->filter(function($appointment) use ($slotStart, $slotEnd) {
                            $apptStart = Carbon::parse($appointment->starts_at);
                            $apptEnd = Carbon::parse($appointment->ends_at);
                            return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                        })->isNotEmpty();

                        if (!$hasConflict && $slotStart->gte(now())) {
                            $availableSlots[] = $slotStart->format('H:i');
                        }

                        $currentSlot->addMinutes($duration);
                    }
                }

                return response()->json([
                    'success' => true,
                    'slots' => $availableSlots
                ]);
            })();
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao listar horários disponíveis', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao listar horários disponíveis: ' . $e->getMessage()
            ], 500);
        }
    }
}
