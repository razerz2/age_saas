<?php

namespace App\Services;

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Appointment;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envia email ao paciente se configurado
     */
    public static function sendEmailToPatient(Patient $patient, $subject, $view, $data = []): void
    {
        try {
            // Verifica se está habilitado enviar email aos pacientes (opt-in, padrão é false)
            $enabled = TenantSetting::get('notifications.send_email_to_patients');
            if ($enabled !== 'true' && $enabled !== true) {
                Log::info('📧 Email não enviado: notificações de email desabilitadas para pacientes');
                return;
            }

            // Verifica se o paciente tem email
            if (empty($patient->email)) {
                Log::warning('📧 Email não enviado: paciente sem email', ['patient_id' => $patient->id]);
                return;
            }

            MailTenantService::send($patient->email, $subject, $view, $data);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email ao paciente', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia WhatsApp ao paciente se configurado
     */
    public static function sendWhatsappToPatient(Patient $patient, $message): void
    {
        try {
            // Verifica se está habilitado enviar WhatsApp aos pacientes (opt-in, padrão é false)
            $enabled = TenantSetting::get('notifications.send_whatsapp_to_patients');
            if ($enabled !== 'true' && $enabled !== true) {
                Log::info('📱 WhatsApp não enviado: notificações de WhatsApp desabilitadas para pacientes');
                return;
            }

            // Verifica se o paciente tem telefone
            if (empty($patient->phone)) {
                Log::warning('📱 WhatsApp não enviado: paciente sem telefone', ['patient_id' => $patient->id]);
                return;
            }

            WhatsappTenantService::send($patient->phone, $message);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar WhatsApp ao paciente', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia email com link do formulário ao paciente
     */
    public static function sendEmailFormLink(Patient $patient, Appointment $appointment, string $url): void
    {
        try {
            // Verifica se está habilitado enviar email aos pacientes
            $enabled = TenantSetting::get('notifications.send_email_to_patients');
            if ($enabled !== 'true' && $enabled !== true) {
                Log::info('📧 Email de formulário não enviado: notificações de email desabilitadas para pacientes');
                return;
            }

            // Verifica se o paciente tem email
            if (empty($patient->email)) {
                Log::warning('📧 Email de formulário não enviado: paciente sem email', ['patient_id' => $patient->id]);
                return;
            }

            // Usa MailTenantService para respeitar configurações do tenant
            MailTenantService::send(
                $patient->email,
                "Formulário Pré-Consulta",
                'emails.form_link',
                ['patient' => $patient, 'appointment' => $appointment, 'url' => $url]
            );
            
            Log::info('📧 Email de formulário enviado', [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email de formulário ao paciente', [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia WhatsApp com link do formulário ao paciente
     */
    public static function sendWhatsappFormLink(Patient $patient, Appointment $appointment, string $url): void
    {
        try {
            // Verifica se está habilitado enviar WhatsApp aos pacientes
            $enabled = TenantSetting::get('notifications.send_whatsapp_to_patients');
            if ($enabled !== 'true' && $enabled !== true) {
                Log::info('📱 WhatsApp de formulário não enviado: notificações de WhatsApp desabilitadas para pacientes');
                return;
            }

            // Verifica se o paciente tem telefone
            if (empty($patient->phone)) {
                Log::warning('📱 WhatsApp de formulário não enviado: paciente sem telefone', ['patient_id' => $patient->id]);
                return;
            }

            $message = "Olá {$patient->full_name}, seu agendamento foi criado!\n";
            $message .= "Antes da consulta, preencha este formulário:\n{$url}";

            WhatsappTenantService::send($patient->phone, $message);
            
            Log::info('📱 WhatsApp de formulário enviado', [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar WhatsApp de formulário ao paciente', [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

