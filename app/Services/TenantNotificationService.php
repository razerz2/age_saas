<?php

namespace App\Services;

use App\Models\Tenant\Notification;
use App\Models\Tenant\TenantSetting;
use App\Services\Tenant\EmailSender;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\WhatsAppSender;
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
        $internalNotificationsEnabled = TenantSetting::isEnabled('notifications.appointments.enabled');
        $emailEnabled = self::isPatientEmailEnabled();
        $whatsappEnabled = self::isPatientWhatsappEnabled();

        $tenant = \App\Models\Platform\Tenant::current();
        $emailProvider = TenantSetting::emailProvider();
        $whatsappProvider = TenantSetting::whatsappProvider();

        Log::info('🔔 Appointment notification dispatch started', [
            'tenant_id' => $tenant?->id,
            'appointment_id' => $appointment->id ?? null,
            'action' => $action,
            'internal_notifications_enabled' => $internalNotificationsEnabled,
            'email_enabled' => $emailEnabled,
            'whatsapp_enabled' => $whatsappEnabled,
            'email_driver' => $emailProvider['driver'] ?? null,
            'email_host_set' => !empty($emailProvider['host'] ?? null),
            'email_from_address' => $emailProvider['from_address'] ?? null,
            'whatsapp_driver' => $whatsappProvider['driver'] ?? null,
            'whatsapp_provider' => $whatsappProvider['provider'] ?? null,
            'waha_base_url' => $whatsappProvider['waha_base_url'] ?? null,
            'waha_session' => $whatsappProvider['waha_session'] ?? null,
            'waha_api_key_set' => !empty($whatsappProvider['waha_api_key'] ?? null),
            'patient_email' => $appointment->patient?->email ?? null,
            'patient_phone' => $appointment->patient?->phone ?? null,
        ]);

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

        $notification = null;
        if ($internalNotificationsEnabled) {
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
        }

        // Envia notificação ao paciente se configurado
        // Apenas para ações relevantes ao paciente
        $actionsToNotifyPatient = ['created', 'cancelled', 'rescheduled', 'scheduled'];
        if (in_array($action, $actionsToNotifyPatient) && ($emailEnabled || $whatsappEnabled)) {
            self::sendAppointmentNotificationToPatient($appointment, $action, $metadata);
        }

        return $notification;
    }

    /**
     * Envia notificacao de agendamento ao paciente (email/WhatsApp)
     */
    private static function sendAppointmentNotificationToPatient(
        $appointment,
        string $action,
        ?array $metadata = null
    ): void {
        try {
            $patient = $appointment->patient;
            if (!$patient) {
                \Log::warning('Paciente nao encontrado para enviar notificacao de agendamento', [
                    'appointment_id' => $appointment->id,
                ]);
                return;
            }

            // Obter tenant atual
            $tenant = \App\Models\Platform\Tenant::current();
            $tenantName = $tenant ? ($tenant->trade_name ?? $tenant->legal_name) : 'Clinica';

            // Obter informacoes do agendamento
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

            $emailEnabled = self::isPatientEmailEnabled();
            $whatsappEnabled = self::isPatientWhatsappEnabled();

            // Enviar por email
            if ($patient->email && $emailEnabled) {
                try {
                    $templateKey = self::resolveTemplateKeyForAction($action, $appointment);
                    $templateSource = 'legacy_hardcoded';
                    $emailSubject = $templates['email_subject'];
                    $emailMessage = $templates['email_body'];

                    if ($templateKey !== null) {
                        try {
                            /** @var NotificationDispatcher $dispatcher */
                            $dispatcher = app(NotificationDispatcher::class);
                            $payloads = $dispatcher->buildMessageForAppointment(
                                $appointment,
                                $templateKey,
                                ['email'],
                                [
                                    'origin' => 'tenant_notification_service',
                                    'event' => 'appointment_' . $action,
                                ]
                            );

                            $emailPayload = $payloads['email'] ?? null;
                            if (is_array($emailPayload) && isset($emailPayload['message'])) {
                                $emailSubject = (string) ($emailPayload['subject'] ?? '');
                                $emailMessage = (string) $emailPayload['message'];
                                $templateSource = (string) ($emailPayload['template_source'] ?? 'default');
                            }
                        } catch (\Throwable $e) {
                            $templateSource = 'legacy_fallback';
                            \Log::warning('Falha ao montar email via NotificationDispatcher. Usando fallback legado.', [
                                'appointment_id' => $appointment->id,
                                'action' => $action,
                                'key' => $templateKey,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    /** @var EmailSender $emailSender */
                    $emailSender = app(EmailSender::class);
                    $sent = $emailSender->send(
                        (string) ($tenant?->id ?? ''),
                        (string) $patient->email,
                        $emailSubject,
                        $emailMessage,
                        [
                            'appointment_id' => (string) $appointment->id,
                            'origin' => 'tenant_notification_service',
                            'event' => 'appointment_' . $action,
                            'key' => $templateKey ?? ('legacy.' . $action),
                            'template_source' => $templateSource,
                        ]
                    );

                    if (!$sent) {
                        \Log::warning('Notificacao de agendamento por email nao enviada.', [
                            'appointment_id' => $appointment->id,
                            'action' => $action,
                            'key' => $templateKey ?? ('legacy.' . $action),
                            'template_source' => $templateSource,
                        ]);
                    }

                    \Log::info('Notificacao de agendamento enviada por email', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'patient_email' => $patient->email,
                        'key' => $templateKey ?? ('legacy.' . $action),
                        'template_source' => $templateSource,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar notificacao de agendamento por email', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Enviar por WhatsApp
            if ($patient->phone && $whatsappEnabled) {
                try {
                    $templateKey = self::resolveTemplateKeyForAction($action, $appointment);
                    $templateSource = 'legacy_hardcoded';
                    $whatsappMessage = $templates['whatsapp_message'];

                    if ($templateKey !== null) {
                        try {
                            /** @var NotificationDispatcher $dispatcher */
                            $dispatcher = app(NotificationDispatcher::class);
                            $payloads = $dispatcher->buildMessageForAppointment(
                                $appointment,
                                $templateKey,
                                ['whatsapp'],
                                [
                                    'origin' => 'tenant_notification_service',
                                    'event' => 'appointment_' . $action,
                                ]
                            );

                            $whatsappPayload = $payloads['whatsapp'] ?? null;
                            if (is_array($whatsappPayload) && isset($whatsappPayload['message'])) {
                                $whatsappMessage = (string) $whatsappPayload['message'];
                                $templateSource = (string) ($whatsappPayload['template_source'] ?? 'default');
                            }
                        } catch (\Throwable $e) {
                            $templateSource = 'legacy_fallback';
                            \Log::warning('Falha ao montar mensagem WhatsApp via NotificationDispatcher. Usando fallback legado.', [
                                'appointment_id' => $appointment->id,
                                'action' => $action,
                                'key' => $templateKey,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    /** @var WhatsAppSender $whatsAppSender */
                    $whatsAppSender = app(WhatsAppSender::class);
                    $sent = $whatsAppSender->send(
                        (string) ($tenant?->id ?? ''),
                        (string) $patient->phone,
                        $whatsappMessage,
                        [
                            'appointment_id' => (string) $appointment->id,
                            'origin' => 'tenant_notification_service',
                            'event' => 'appointment_' . $action,
                            'key' => $templateKey ?? ('legacy.' . $action),
                            'template_source' => $templateSource,
                        ]
                    );

                    if (!$sent) {
                        \Log::warning('Notificacao de agendamento por WhatsApp nao enviada.', [
                            'appointment_id' => $appointment->id,
                            'action' => $action,
                            'key' => $templateKey ?? ('legacy.' . $action),
                            'template_source' => $templateSource,
                        ]);
                    }

                    \Log::info('Notificacao de agendamento enviada por WhatsApp', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'patient_phone' => $patient->phone,
                        'key' => $templateKey ?? ('legacy.' . $action),
                        'template_source' => $templateSource,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar notificacao de agendamento por WhatsApp', [
                        'appointment_id' => $appointment->id,
                        'action' => $action,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao enviar notificacao de agendamento ao paciente', [
                'appointment_id' => $appointment->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private static function isPatientEmailEnabled(): bool
    {
        $enabled = TenantSetting::get('notifications.send_email_to_patients');
        return $enabled === 'true' || $enabled === true;
    }

    private static function isPatientWhatsappEnabled(): bool
    {
        $enabled = TenantSetting::get('notifications.send_whatsapp_to_patients');
        return $enabled === 'true' || $enabled === true;
    }

    private static function resolveTemplateKeyForAction(string $action, $appointment): ?string
    {
        return match ($action) {
            'scheduled' => 'appointment.confirmed',
            'cancelled' => 'appointment.canceled',
            'created' => (($appointment->status ?? null) === 'pending_confirmation')
                ? 'appointment.pending_confirmation'
                : 'appointment.confirmed',
            default => null,
        };
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

