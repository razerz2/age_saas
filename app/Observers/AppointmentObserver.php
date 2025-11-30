<?php

namespace App\Observers;

use App\Models\Tenant\Appointment;
use App\Services\TenantNotificationService;
use App\Services\Tenant\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // Carrega os relacionamentos necessários
        $appointment->load(['patient', 'calendar.doctor.user']);
        
        TenantNotificationService::notifyAppointment('created', $appointment);

        // IMPORTANTE: Agendamentos de recorrência NÃO devem ser sincronizados individualmente
        // A recorrência em si deve ser sincronizada como um evento recorrente no Google Calendar
        // Isso evita criação infinita de eventos para recorrências sem data fim
        if ($appointment->recurring_appointment_id) {
            // Não sincroniza agendamentos individuais de recorrência
            // A recorrência deve ser sincronizada separadamente quando criada/editada
            return;
        }

        // Sincronizar com Google Calendar se o médico tiver token
        try {
            $this->googleCalendarService->syncEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar agendamento com Google Calendar (Observer)', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        // Carrega os relacionamentos necessários
        $appointment->load(['patient', 'calendar.doctor.user']);
        
        // Verifica se o status mudou
        if ($appointment->wasChanged('status')) {
            $oldStatus = $appointment->getOriginal('status');
            $newStatus = $appointment->status;
            
            // Notifica baseado no novo status
            $actionMap = [
                'canceled' => 'cancelled',
                'rescheduled' => 'rescheduled',
                'scheduled' => 'scheduled',
                'attended' => 'attended',
                'no_show' => 'no_show',
            ];
            
            if (isset($actionMap[$newStatus])) {
                TenantNotificationService::notifyAppointment(
                    $actionMap[$newStatus], 
                    $appointment,
                    ['old_status' => $oldStatus, 'new_status' => $newStatus]
                );
            }
        } else {
            // Se não mudou o status, notifica apenas como atualizado
            // (pode ter mudado horário, notas, etc)
            if ($appointment->wasChanged(['starts_at', 'ends_at', 'notes'])) {
                TenantNotificationService::notifyAppointment('updated', $appointment);
            }
        }

        // IMPORTANTE: Agendamentos de recorrência NÃO devem ser sincronizados individualmente
        if ($appointment->recurring_appointment_id) {
            // Não sincroniza agendamentos individuais de recorrência
            return;
        }

        // Se status mudou para "canceled", apenas remover do Google Calendar
        if ($appointment->wasChanged('status') && $appointment->status === 'canceled') {
            try {
                $this->googleCalendarService->deleteEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao remover agendamento cancelado do Google Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return;
        }

        // Sincronizar com Google Calendar se o médico tiver token
        // Sincroniza quando há mudanças relevantes (horário, status, etc)
        // IMPORTANTE: Ignora mudanças apenas no google_event_id para evitar loop infinito
        // ESTRATÉGIA: Para edição, deletar e criar novo (mais simples e confiável)
        $changedFields = array_keys($appointment->getChanges());
        $relevantFields = ['starts_at', 'ends_at', 'status', 'notes', 'patient_id', 'calendar_id'];
        
        // Verifica se houve mudança em campos relevantes (ignorando google_event_id)
        $hasRelevantChange = false;
        foreach ($relevantFields as $field) {
            if (in_array($field, $changedFields)) {
                $hasRelevantChange = true;
                break;
            }
        }
        
        // Se a única mudança foi no google_event_id, ignora (foi atualização do próprio serviço)
        if (!$hasRelevantChange && count($changedFields) === 1 && in_array('google_event_id', $changedFields)) {
            return;
        }
        
        if ($hasRelevantChange) {
            try {
                $this->googleCalendarService->syncEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar agendamento com Google Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        // IMPORTANTE: Agendamentos de recorrência NÃO devem ser removidos individualmente
        // A remoção da recorrência deve ser feita separadamente
        if ($appointment->recurring_appointment_id) {
            return;
        }

        // Remover do Google Calendar se existir
        try {
            $this->googleCalendarService->deleteEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao remover agendamento do Google Calendar (Observer)', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

