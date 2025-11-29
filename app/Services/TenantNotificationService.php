<?php

namespace App\Services;

use App\Models\Tenant\Notification;
use App\Models\Tenant\TenantSetting;

class TenantNotificationService
{
    /**
     * Cria uma notificação
     */
    public static function create(
        string $type,
        string $title,
        string $message,
        string $level = 'info',
        ?string $relatedId = null,
        ?string $relatedType = null,
        ?array $metadata = null
    ): Notification {
        return Notification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'level' => $level,
            'status' => 'new',
            'related_id' => $relatedId,
            'related_type' => $relatedType,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Cria notificação para agendamento
     */
    public static function notifyAppointment(
        string $action, // 'created', 'updated', 'cancelled', 'rescheduled', etc.
        $appointment,
        ?array $metadata = null
    ): ?Notification {
        // Verifica se notificações de agendamento estão habilitadas
        if (!TenantSetting::isEnabled('notifications.appointments.enabled')) {
            return null;
        }

        $messages = [
            'created' => [
                'title' => 'Novo agendamento criado',
                'message' => "Um novo agendamento foi criado para {$appointment->patient->full_name} em " . 
                           $appointment->starts_at->format('d/m/Y H:i'),
                'level' => 'info',
            ],
            'updated' => [
                'title' => 'Agendamento atualizado',
                'message' => "O agendamento de {$appointment->patient->full_name} foi atualizado.",
                'level' => 'info',
            ],
            'cancelled' => [
                'title' => 'Agendamento cancelado',
                'message' => "O agendamento de {$appointment->patient->full_name} foi cancelado.",
                'level' => 'warning',
            ],
            'rescheduled' => [
                'title' => 'Agendamento reagendado',
                'message' => "O agendamento de {$appointment->patient->full_name} foi reagendado para " . 
                           $appointment->starts_at->format('d/m/Y H:i'),
                'level' => 'info',
            ],
            'scheduled' => [
                'title' => 'Agendamento agendado',
                'message' => "Agendamento confirmado para {$appointment->patient->full_name} em " . 
                           $appointment->starts_at->format('d/m/Y H:i'),
                'level' => 'success',
            ],
            'attended' => [
                'title' => 'Agendamento atendido',
                'message' => "O agendamento de {$appointment->patient->full_name} foi marcado como atendido.",
                'level' => 'success',
            ],
            'no_show' => [
                'title' => 'Paciente não compareceu',
                'message' => "O paciente {$appointment->patient->full_name} não compareceu ao agendamento.",
                'level' => 'warning',
            ],
        ];

        if (!isset($messages[$action])) {
            return null;
        }

        $data = $messages[$action];

        // Adiciona informações adicionais ao metadata
        $metadata = array_merge($metadata ?? [], [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'patient_name' => $appointment->patient->full_name ?? null,
            'starts_at' => $appointment->starts_at?->toDateTimeString(),
            'status' => $appointment->status,
        ]);

        return self::create(
            'appointment',
            $data['title'],
            $data['message'],
            $data['level'],
            $appointment->id,
            'App\Models\Tenant\Appointment',
            $metadata
        );
    }

    /**
     * Cria notificação para resposta de formulário
     */
    public static function notifyFormResponse($formResponse, ?array $metadata = null): ?Notification
    {
        // Verifica se notificações de formulário estão habilitadas
        if (!TenantSetting::isEnabled('notifications.form_responses.enabled')) {
            return null;
        }

        $form = $formResponse->form;
        $patient = $formResponse->patient;
        
        $patientName = $patient->full_name ?? 'Paciente';
        $formName = $form->name ?? 'Formulário';

        $title = 'Nova resposta de formulário';
        $message = "O paciente {$patientName} respondeu o formulário '{$formName}'.";

        // Adiciona informações adicionais ao metadata
        $metadata = array_merge($metadata ?? [], [
            'form_response_id' => $formResponse->id,
            'form_id' => $form->id ?? null,
            'form_name' => $formName,
            'patient_id' => $formResponse->patient_id,
            'patient_name' => $patientName,
            'submitted_at' => $formResponse->submitted_at?->toDateTimeString(),
        ]);

        return self::create(
            'form_response',
            $title,
            $message,
            'success',
            $formResponse->id,
            'App\Models\Tenant\FormResponse',
            $metadata
        );
    }

    /**
     * Conta notificações não lidas
     */
    public static function unreadCount(): int
    {
        return Notification::unread()->count();
    }

    /**
     * Marca todas as notificações como lidas
     */
    public static function markAllAsRead(): int
    {
        return Notification::unread()
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }
}

