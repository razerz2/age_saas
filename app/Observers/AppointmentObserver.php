<?php

namespace App\Observers;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\Form;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Platform\Tenant;
use App\Jobs\Tenant\SendAppointmentNotificationsJob;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AppointmentObserver
{
    protected GoogleCalendarService $googleCalendarService;
    protected AppleCalendarService $appleCalendarService;
    protected NotificationDispatcher $notificationDispatcher;

    public function __construct(
        GoogleCalendarService $googleCalendarService,
        AppleCalendarService $appleCalendarService,
        NotificationDispatcher $notificationDispatcher
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->appleCalendarService = $appleCalendarService;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // Carrega os relacionamentos necessários
        $appointment->load(['patient', 'calendar.doctor.user', 'specialty']);
        
        // Criar instruções vazias automaticamente se for consulta online
        if ($appointment->appointment_mode === 'online') {
            try {
                OnlineAppointmentInstruction::create([
                    'id' => Str::uuid(),
                    'appointment_id' => $appointment->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao criar instruções online automaticamente', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $metadata = [
            'origin' => (string) ($appointment->origin ?? ''),
        ];

        if ($this->isWhatsAppBotOrigin($appointment)) {
            $metadata['suppress_patient_channels'] = ['whatsapp'];
            $metadata['notification_context'] = 'whatsapp_bot_inline_confirmation';
        }

        $this->dispatchAppointmentNotificationJob('created', $appointment, $metadata);

        // Dispara notificação específica de formulário para paciente quando houver formulário ativo/vinculado.
        $form = Form::getFormForAppointment($appointment);
        if ($form) {
            try {
                $formRequestMeta = [
                    'event' => 'appointment_form_requested_patient',
                    'origin' => (string) ($appointment->origin ?? ''),
                ];

                if ($this->isWhatsAppBotOrigin($appointment)) {
                    $formRequestMeta['suppress_patient_channels'] = ['whatsapp'];
                }

                $this->notificationDispatcher->dispatchAppointment(
                    $appointment,
                    'appointment.form_requested.patient',
                    $formRequestMeta
                );
            } catch (\Exception $e) {
                Log::error('Erro ao disparar notificação de solicitação de formulário após criar agendamento', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

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

        // Sincronizar com Apple Calendar se o médico tiver token
        try {
            $this->appleCalendarService->syncEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar agendamento com Apple Calendar (Observer)', [
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
                $this->dispatchAppointmentNotificationJob(
                    $actionMap[$newStatus],
                    $appointment,
                    ['old_status' => $oldStatus, 'new_status' => $newStatus]
                );

                if ($newStatus === 'rescheduled') {
                    $this->notificationDispatcher->dispatchAppointment(
                        $appointment,
                        'appointment.rescheduled.doctor',
                        [
                            'event' => 'appointment_rescheduled_doctor',
                            'origin' => (string) ($appointment->origin ?? ''),
                        ]
                    );
                }
            }
        } else {
            // Se não mudou o status, notifica apenas como atualizado
            // (pode ter mudado horário, notas, etc)
            if ($appointment->wasChanged(['starts_at', 'ends_at', 'notes'])) {
                $this->dispatchAppointmentNotificationJob('updated', $appointment);
            }
        }

        // IMPORTANTE: Agendamentos de recorrência NÃO devem ser sincronizados individualmente
        if ($appointment->recurring_appointment_id) {
            // Não sincroniza agendamentos individuais de recorrência
            return;
        }

        // Se status mudou para "canceled", remover dos calendários externos
        if ($appointment->wasChanged('status') && $appointment->status === 'canceled') {
            try {
                $this->googleCalendarService->deleteEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao remover agendamento cancelado do Google Calendar (Observer)', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
            try {
                $this->appleCalendarService->deleteEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao remover agendamento cancelado do Apple Calendar (Observer)', [
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
        
        // Se a única mudança foi nos event_ids, ignora (foi atualização do próprio serviço)
        if (!$hasRelevantChange && count($changedFields) === 1 && 
            (in_array('google_event_id', $changedFields) || in_array('apple_event_id', $changedFields))) {
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
            try {
                $this->appleCalendarService->syncEvent($appointment);
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar agendamento com Apple Calendar (Observer)', [
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

        // Remover dos calendários externos se existir
        try {
            $this->googleCalendarService->deleteEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao remover agendamento do Google Calendar (Observer)', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
        try {
            $this->appleCalendarService->deleteEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao remover agendamento do Apple Calendar (Observer)', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function dispatchAppointmentNotificationJob(
        string $action,
        Appointment $appointment,
        ?array $metadata = null
    ): void {
        $tenant = Tenant::current();
        if (!$tenant) {
            Log::warning('Tenant atual não encontrado para enfileirar notificação de agendamento', [
                'appointment_id' => $appointment->id,
                'action' => $action,
            ]);
            return;
        }

        $queueConnection = (string) config('queue.default', 'sync');
        $queueName = (string) config("queue.connections.{$queueConnection}.queue", 'default');

        $pendingDispatch = SendAppointmentNotificationsJob::dispatch(
            $tenant->id,
            $appointment->id,
            $action,
            $metadata
        );

        if (method_exists($pendingDispatch, 'afterCommit')) {
            $pendingDispatch->afterCommit();
        }

        if (in_array($queueConnection, ['database', 'redis'], true)) {
            Log::info('📬 Appointment notification job enqueued', [
                'tenant_id' => $tenant->id,
                'appointment_id' => $appointment->id,
                'action' => $action,
                'queue_connection' => $queueConnection,
                'queue' => $queueName,
            ]);
        } else {
            Log::info('📬 Appointment notification dispatch scheduled', [
                'tenant_id' => $tenant->id,
                'appointment_id' => $appointment->id,
                'action' => $action,
                'queue_connection' => $queueConnection,
            ]);
        }
    }

    private function isWhatsAppBotOrigin(Appointment $appointment): bool
    {
        return trim(strtolower((string) ($appointment->origin ?? ''))) === Appointment::ORIGIN_WHATSAPP_BOT;
    }
}
