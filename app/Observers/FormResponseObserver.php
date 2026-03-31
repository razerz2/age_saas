<?php

namespace App\Observers;

use App\Models\Tenant\FormResponse;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\TenantNotificationService;
use Illuminate\Support\Facades\Log;

class FormResponseObserver
{
    /**
     * Handle the FormResponse "created" event.
     */
    public function created(FormResponse $formResponse): void
    {
        // Carrega os relacionamentos necessários
        $formResponse->load(['form', 'patient', 'appointment.doctor.user', 'appointment.onlineInstructions']);
        
        TenantNotificationService::notifyFormResponse($formResponse);

        try {
            $onlineAppointment = $formResponse->appointment !== null
                && (string) ($formResponse->appointment->appointment_mode ?? '') === 'online';

            app(NotificationDispatcher::class)->dispatchFormResponse(
                $formResponse,
                $onlineAppointment ? 'online_appointment.form_response_submitted.doctor' : 'form.response_submitted.doctor',
                [
                    'event' => $onlineAppointment ? 'online_appointment_form_response_submitted' : 'form_response_submitted',
                    'origin' => 'form_response_observer',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Falha ao despachar notificacao de formulario para medico.', [
                'form_response_id' => (string) $formResponse->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
