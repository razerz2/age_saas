<?php

namespace App\Observers;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\Form;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Platform\Tenant;
use App\Services\TenantNotificationService;
use App\Services\NotificationService;
use App\Services\Tenant\GoogleCalendarService;
use App\Services\Tenant\AppleCalendarService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AppointmentObserver
{
    protected GoogleCalendarService $googleCalendarService;
    protected AppleCalendarService $appleCalendarService;

    public function __construct(
        GoogleCalendarService $googleCalendarService,
        AppleCalendarService $appleCalendarService
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->appleCalendarService = $appleCalendarService;
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
        
        TenantNotificationService::notifyAppointment('created', $appointment);

        // Enviar link do formulário se existir formulário ativo
        $form = Form::getFormForAppointment($appointment);
        if ($form) {
            try {
                // Obtém o tenant atual
                $tenant = Tenant::current();
                if (!$tenant) {
                    Log::warning('Não foi possível obter tenant atual para enviar link do formulário', [
                        'appointment_id' => $appointment->id
                    ]);
                } else {
                    // Gera URL do formulário usando tenant_route
                    $url = tenant_route(
                        $tenant,
                        'public.form.response.create',
                        [
                            'form' => $form->id,
                            'appointment' => $appointment->id
                        ]
                    );

                    // Verifica configurações do tenant
                    $settings = TenantSetting::getAll();

                    // Envia email se configurado
                    if (($settings['notifications.form_send_email'] ?? false) === 'true' || 
                        ($settings['notifications.form_send_email'] ?? false) === true) {
                        NotificationService::sendEmailFormLink($appointment->patient, $appointment, $url);
                    }

                    // Envia WhatsApp se configurado
                    if (($settings['notifications.form_send_whatsapp'] ?? false) === 'true' || 
                        ($settings['notifications.form_send_whatsapp'] ?? false) === true) {
                        NotificationService::sendWhatsappFormLink($appointment->patient, $appointment, $url);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar link do formulário após criar agendamento', [
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
}

