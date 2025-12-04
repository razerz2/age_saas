<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\OnlineAppointmentInstruction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProcessRecurringAppointmentsCommand extends Command
{
    protected $signature = 'recurring-appointments:process';
    protected $description = 'Processa agendamentos recorrentes e gera sessões automaticamente.';

    public function handle()
    {
        $this->info("🚀 Iniciando processamento de agendamentos recorrentes...");

        $tenants = Tenant::all();
        $totalProcessed = 0;
        $totalCreated = 0;
        $totalErrors = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->makeCurrent();
                
                $this->info("📋 Processando tenant: {$tenant->name}");

                $processed = $this->processTenantRecurringAppointments($tenant);
                $totalProcessed += $processed['processed'];
                $totalCreated += $processed['created'];
                $totalErrors += $processed['errors'];

            } catch (\Exception $e) {
                Log::error("Erro ao processar tenant {$tenant->id}: " . $e->getMessage());
                $totalErrors++;
                continue;
            }
        }

        $this->info("✅ Processamento concluído!");
        $this->info("   - Recorrências processadas: {$totalProcessed}");
        $this->info("   - Sessões criadas: {$totalCreated}");
        $this->info("   - Erros: {$totalErrors}");

        return Command::SUCCESS;
    }

    private function processTenantRecurringAppointments(Tenant $tenant): array
    {
        $processed = 0;
        $created = 0;
        $errors = 0;

        // Buscar todas as recorrências ativas
        $recurringAppointments = RecurringAppointment::with(['rules', 'doctor.calendars', 'appointmentType'])
            ->where('active', true)
            ->get();

        foreach ($recurringAppointments as $recurring) {
            try {
                // Verificar se a recorrência ainda está ativa
                if (!$recurring->isActive()) {
                    continue;
                }

                // Processar cada regra
                foreach ($recurring->rules as $rule) {
                    $sessionsCreated = $this->processRule($recurring, $rule);
                    $created += $sessionsCreated;
                }

                $processed++;

            } catch (\Exception $e) {
                Log::error("Erro ao processar recorrência {$recurring->id}: " . $e->getMessage());
                $errors++;
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'errors' => $errors,
        ];
    }

    private function processRule(RecurringAppointment $recurring, RecurringAppointmentRule $rule): int
    {
        $created = 0;
        $today = Carbon::today();
        $startDate = Carbon::parse($recurring->start_date);

        // Buscar calendário do médico (pegar o primeiro disponível)
        $calendar = Calendar::where('doctor_id', $recurring->doctor_id)->first();
        if (!$calendar) {
            Log::warning("Médico {$recurring->doctor_id} não possui calendário");
            return 0;
        }

        // Calcular próxima data válida baseada na regra
        $nextDate = $this->calculateNextDate($rule, $startDate, $today);

        // Verificar limites
        if (!$this->checkLimits($recurring, $nextDate)) {
            return 0;
        }

        // Verificar se já existe sessão neste dia para esta recorrência
        $existingAppointment = Appointment::where('recurring_appointment_id', $recurring->id)
            ->whereDate('starts_at', $nextDate->format('Y-m-d'))
            ->first();

        if ($existingAppointment) {
            return 0; // Já existe sessão neste dia
        }

        // Verificar disponibilidade do médico
        if (!$this->isTimeAvailable($recurring->doctor_id, $nextDate, $rule->start_time, $rule->end_time)) {
            Log::info("Horário não disponível para recorrência {$recurring->id} em {$nextDate->format('Y-m-d')}");
            return 0;
        }

        // Criar appointment
        $appointment = $this->createAppointment($recurring, $calendar, $nextDate, $rule);
        if ($appointment) {
            $created++;
        }

        return $created;
    }

    private function calculateNextDate(RecurringAppointmentRule $rule, Carbon $startDate, Carbon $today): Carbon
    {
        $weekdayNumber = $rule->getWeekdayNumber();
        
        // Encontrar próxima ocorrência do dia da semana
        $nextDate = $today->copy();
        
        // Se hoje é o dia da semana e ainda não passou do horário, usar hoje
        if ($nextDate->dayOfWeek === $weekdayNumber) {
            $currentTime = Carbon::now()->format('H:i');
            if ($currentTime < $rule->start_time) {
                return $nextDate;
            }
        }

        // Calcular dias até o próximo dia da semana
        $daysUntilWeekday = ($weekdayNumber - $nextDate->dayOfWeek + 7) % 7;
        if ($daysUntilWeekday === 0) {
            $daysUntilWeekday = 7; // Próxima semana
        }

        $nextDate->addDays($daysUntilWeekday);

        // Garantir que a data não seja anterior à data inicial da recorrência
        if ($nextDate->lt($startDate)) {
            // Se a data calculada é anterior à data inicial, começar da data inicial
            $nextDate = $startDate->copy();
            
            // Ajustar para o dia da semana correto
            $daysToAdd = ($weekdayNumber - $nextDate->dayOfWeek + 7) % 7;
            if ($daysToAdd > 0) {
                $nextDate->addDays($daysToAdd);
            }
        }

        return $nextDate;
    }

    private function checkLimits(RecurringAppointment $recurring, Carbon $date): bool
    {
        // Verificar data inicial
        if ($date->lt($recurring->start_date)) {
            return false;
        }

        // Verificar data final
        if ($recurring->end_type === 'date' && $recurring->end_date && $date->gt($recurring->end_date)) {
            return false;
        }

        // Verificar total de sessões
        if ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
            $generatedCount = $recurring->getGeneratedSessionsCount();
            if ($generatedCount >= $recurring->total_sessions) {
                return false;
            }
        }

        return true;
    }

    private function isTimeAvailable($doctorId, Carbon $date, string $startTime, string $endTime): bool
    {
        // Verificar business hours
        $weekday = $date->dayOfWeek;
        $businessHour = BusinessHour::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->first();

        if (!$businessHour) {
            return false;
        }

        // Verificar conflitos com appointments existentes
        $calendars = Calendar::where('doctor_id', $doctorId)->pluck('id');
        $conflictingAppointment = Appointment::whereIn('calendar_id', $calendars)
            ->whereDate('starts_at', $date->format('Y-m-d'))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->where(function($query) use ($date, $startTime, $endTime) {
                $query->where(function($q) use ($date, $startTime, $endTime) {
                    $q->whereTime('starts_at', '<', $endTime)
                      ->whereTime('ends_at', '>', $startTime);
                });
            })
            ->first();

        if ($conflictingAppointment) {
            return false;
        }

        // Verificar conflitos com recorrências ativas (não incluindo a recorrência atual)
        // Esta verificação é feita no método isSlotBlockedByRecurring do AppointmentController
        // Não precisamos verificar aqui pois estamos criando apenas uma sessão por vez

        return true;
    }

    private function createAppointment(RecurringAppointment $recurring, Calendar $calendar, Carbon $date, RecurringAppointmentRule $rule): ?Appointment
    {
        try {
            $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $rule->start_time);
            $endDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $rule->end_time);

            $appointment = Appointment::create([
                'id' => Str::uuid(),
                'calendar_id' => $calendar->id,
                'doctor_id' => $calendar->doctor_id, // Garantir que doctor_id está definido
                'appointment_type' => $recurring->appointment_type_id,
                'patient_id' => $recurring->patient_id,
                'specialty_id' => null, // Pode ser adicionado depois se necessário
                'starts_at' => $startDateTime,
                'ends_at' => $endDateTime,
                'status' => 'scheduled',
                'recurring_appointment_id' => $recurring->id,
                'appointment_mode' => $recurring->appointment_mode ?? 'presencial',
            ]);

            // Criar instruções vazias automaticamente se for consulta online
            if ($appointment->appointment_mode === 'online') {
                try {
                    OnlineAppointmentInstruction::create([
                        'id' => Str::uuid(),
                        'appointment_id' => $appointment->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao criar instruções online automaticamente para recorrência', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $appointment;

        } catch (\Exception $e) {
            Log::error("Erro ao criar appointment para recorrência {$recurring->id}: " . $e->getMessage());
            return null;
        }
    }
}

