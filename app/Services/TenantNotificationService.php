<?php

namespace App\Services;

use App\Models\Tenant\Notification;
use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Log;

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
     * Cria notificação para agendamento e envia aos pacientes se configurado
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

        // Carrega relacionamentos necessários
        if (!$appointment->relationLoaded('patient')) {
            $appointment->load('patient');
        }
        if (!$appointment->relationLoaded('calendar')) {
            $appointment->load('calendar');
        }
        if (!$appointment->relationLoaded('calendar.doctor')) {
            $appointment->load('calendar.doctor');
        }
        if (!$appointment->relationLoaded('specialty')) {
            $appointment->load('specialty');
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

        // Cria notificação interna
        $notification = self::create(
            'appointment',
            $data['title'],
            $data['message'],
            $data['level'],
            $appointment->id,
            'App\Models\Tenant\Appointment',
            $metadata
        );

        // Envia notificação ao paciente se configurado
        // Apenas para ações relevantes ao paciente
        $actionsToNotifyPatient = ['created', 'cancelled', 'rescheduled', 'scheduled'];
        if (in_array($action, $actionsToNotifyPatient)) {
            self::sendAppointmentNotificationToPatient($appointment, $action, $metadata);
        }

        return $notification;
    }

    /**
     * Envia notificação de agendamento ao paciente (email/WhatsApp)
     */
    private static function sendAppointmentNotificationToPatient(
        $appointment,
        string $action,
        ?array $metadata = null
    ): void {
        try {
            $patient = $appointment->patient;
            if (!$patient) {
                \Log::warning('Paciente não encontrado para enviar notificação de agendamento', [
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            // Obter tenant atual
            $tenant = \App\Models\Platform\Tenant::current();
            $tenantName = $tenant ? ($tenant->trade_name ?? $tenant->legal_name) : 'Clínica';

            // Obter informações do agendamento
            $doctorName = $appointment->calendar->doctor->user->name ?? 'Dr(a).';
            $specialtyName = $appointment->specialty->name ?? '';
            $appointmentDate = $appointment->starts_at->format('d/m/Y');
            $appointmentTime = $appointment->starts_at->format('H:i');
            $appointmentMode = $appointment->appointment_mode === 'online' ? 'Online' : 'Presencial';

            // Templates de mensagens
            $templates = self::getAppointmentTemplates($action, [
                'patient_name' => $patient->full_name,
                'tenant_name' => $tenantName,
                'doctor_name' => $doctorName,
                'specialty_name' => $specialtyName,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'appointment_datetime' => $appointment->starts_at->format('d/m/Y H:i'),
                'appointment_mode' => $appointmentMode,
                'old_status' => $metadata['old_status'] ?? null,
                'new_status' => $metadata['new_status'] ?? null,
            ]);

            // Enviar por email
            if ($patient->email && TenantSetting::isEnabled('notifications.send_email_to_patients')) {
                try {
                    $emailService = app(\App\Services\MailTenantService::class);
                    $emailService->send(
                        $patient->email,
                        $templates['email_subject'],
                        $templates['email_body']
                    );

                    \Log::info('Notificação de agendamento enviada por email', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'patient_email' => $patient->email,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar notificação de agendamento por email', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Enviar por WhatsApp
            if ($patient->phone && TenantSetting::isEnabled('notifications.send_whatsapp_to_patients')) {
                try {
                    $whatsappService = app(\App\Services\WhatsappTenantService::class);
                    $whatsappService->send(
                        $patient->phone,
                        $templates['whatsapp_message']
                    );

                    \Log::info('Notificação de agendamento enviada por WhatsApp', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'patient_phone' => $patient->phone,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar notificação de agendamento por WhatsApp', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao enviar notificação de agendamento ao paciente', [
                'appointment_id' => $appointment->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Retorna templates de mensagens para notificações de agendamento
     */
    private static function getAppointmentTemplates(string $action, array $data): array
    {
        $patientName = $data['patient_name'];
        $tenantName = $data['tenant_name'];
        $doctorName = $data['doctor_name'];
        $specialtyName = $data['specialty_name'];
        $appointmentDate = $data['appointment_date'];
        $appointmentTime = $data['appointment_time'];
        $appointmentDateTime = $data['appointment_datetime'];
        $appointmentMode = $data['appointment_mode'];

        $templates = [
            'created' => [
                'email_subject' => "Agendamento Confirmado - {$tenantName}",
                'email_body' => "Olá {$patientName},\n\n" .
                    "Seu agendamento foi confirmado!\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
                'whatsapp_message' => "Olá {$patientName}! 👋\n\n" .
                    "✅ Seu agendamento foi confirmado!\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
            ],
            'cancelled' => [
                'email_subject' => "Agendamento Cancelado - {$tenantName}",
                'email_body' => "Olá {$patientName},\n\n" .
                    "Infelizmente, seu agendamento foi cancelado.\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n\n" .
                    "Entre em contato conosco para reagendar, se desejar.\n\n" .
                    "Atenciosamente,\n{$tenantName}",
                'whatsapp_message' => "Olá {$patientName}! 👋\n\n" .
                    "❌ Seu agendamento foi cancelado.\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n\n" .
                    "Entre em contato conosco para reagendar, se desejar.\n\n" .
                    "Atenciosamente,\n{$tenantName}",
            ],
            'rescheduled' => [
                'email_subject' => "Agendamento Reagendado - {$tenantName}",
                'email_body' => "Olá {$patientName},\n\n" .
                    "Seu agendamento foi reagendado.\n\n" .
                    "📅 Nova Data: {$appointmentDate}\n" .
                    "🕐 Novo Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
                'whatsapp_message' => "Olá {$patientName}! 👋\n\n" .
                    "🔄 Seu agendamento foi reagendado!\n\n" .
                    "📅 Nova Data: {$appointmentDate}\n" .
                    "🕐 Novo Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
            ],
            'scheduled' => [
                'email_subject' => "Agendamento Confirmado - {$tenantName}",
                'email_body' => "Olá {$patientName},\n\n" .
                    "Seu agendamento foi confirmado!\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
                'whatsapp_message' => "Olá {$patientName}! 👋\n\n" .
                    "✅ Seu agendamento foi confirmado!\n\n" .
                    "📅 Data: {$appointmentDate}\n" .
                    "🕐 Horário: {$appointmentTime}\n" .
                    "👨‍⚕️ Profissional: {$doctorName}\n" .
                    ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                    "📍 Modalidade: {$appointmentMode}\n\n" .
                    "Atenciosamente,\n{$tenantName}",
            ],
        ];

        return $templates[$action] ?? [
            'email_subject' => "Atualização de Agendamento - {$tenantName}",
            'email_body' => "Olá {$patientName},\n\nSeu agendamento foi atualizado.\n\nAtenciosamente,\n{$tenantName}",
            'whatsapp_message' => "Olá {$patientName}! Seu agendamento foi atualizado. Atenciosamente, {$tenantName}",
        ];
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

    /**
     * Envia link de pagamento por email e/ou WhatsApp
     * 
     * @param \App\Models\Tenant\FinancialCharge $charge
     * @return void
     */
    public static function sendPaymentLink(\App\Models\Tenant\FinancialCharge $charge): void
    {
        try {
            $patient = $charge->patient;
            $appointment = $charge->appointment;

            if (!$patient || !$appointment) {
                \Log::warning('Não foi possível enviar link de pagamento: paciente ou agendamento não encontrado', [
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Obter tenant atual
            $tenant = \App\Models\Platform\Tenant::current();
            $tenantName = $tenant ? ($tenant->trade_name ?? $tenant->legal_name) : 'Clínica';

            // Formatar valor
            $amount = number_format($charge->amount, 2, ',', '.');
            $paymentLink = $charge->payment_link;

            if (!$paymentLink) {
                \Log::warning('Link de pagamento não disponível', [
                    'charge_id' => $charge->id,
                ]);
                return;
            }

            // Enviar por email se paciente tiver email
            if ($patient->email && TenantSetting::isEnabled('notifications.send_email_to_patients')) {
                try {
                    $emailService = app(\App\Services\MailTenantService::class);
                    
                    $subject = "Link de Pagamento - {$tenantName}";
                    $message = "Olá {$patient->full_name},\n\n";
                    $message .= "Seu agendamento foi confirmado!\n\n";
                    $message .= "Para garantir sua consulta, realize o pagamento através do link abaixo:\n\n";
                    $message .= "Valor: R$ {$amount}\n";
                    $message .= "Link: {$paymentLink}\n\n";
                    $message .= "Data da consulta: " . $appointment->starts_at->format('d/m/Y H:i') . "\n\n";
                    $message .= "Atenciosamente,\n{$tenantName}";

                    // Usar o serviço de email do tenant
                    $emailService->send(
                        $patient->email,
                        $subject,
                        $message
                    );

                    \Log::info('Link de pagamento enviado por email', [
                        'charge_id' => $charge->id,
                        'patient_email' => $patient->email,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar link de pagamento por email', [
                        'charge_id' => $charge->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Enviar por WhatsApp se paciente tiver telefone
            if ($patient->phone && TenantSetting::isEnabled('notifications.send_whatsapp_to_patients')) {
                try {
                    $whatsappService = app(\App\Services\WhatsappTenantService::class);
                    
                    $message = "Olá {$patient->full_name}!\n\n";
                    $message .= "Seu agendamento foi confirmado!\n\n";
                    $message .= "Para garantir sua consulta, realize o pagamento:\n\n";
                    $message .= "💰 Valor: R$ {$amount}\n";
                    $message .= "🔗 Link: {$paymentLink}\n\n";
                    $message .= "📅 Data: " . $appointment->starts_at->format('d/m/Y H:i') . "\n\n";
                    $message .= "Atenciosamente,\n{$tenantName}";

                    $whatsappService->send(
                        $patient->phone,
                        $message
                    );

                    \Log::info('Link de pagamento enviado por WhatsApp', [
                        'charge_id' => $charge->id,
                        'patient_phone' => $patient->phone,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar link de pagamento por WhatsApp', [
                        'charge_id' => $charge->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao enviar link de pagamento', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

