<?php

namespace App\Observers;

use App\Models\Tenant\Appointment;
use App\Services\TenantNotificationService;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // Carrega os relacionamentos necessários
        $appointment->load(['patient', 'calendar.doctor.user']);
        
        TenantNotificationService::notifyAppointment('created', $appointment);
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
    }
}

