<?php

namespace App\Support;

class TenantDefaultNotificationTemplateCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.pending_confirmation',
                'title' => 'Agendamento pendente de confirmacao',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} esta pendente de confirmacao.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}\nModalidade: {{appointment.mode}}\n\nConfirmar: {{links.appointment_confirm}}\nCancelar: {{links.appointment_cancel}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                    'appointment.mode',
                    'links.appointment_confirm',
                    'links.appointment_cancel',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.confirmed',
                'title' => 'Agendamento confirmado',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} foi confirmado.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                    'appointment.mode',
                    'links.appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.canceled',
                'title' => 'Agendamento cancelado',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} foi cancelado.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.expired',
                'title' => 'Agendamento expirado',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nO prazo para confirmar seu agendamento em {{clinic.name}} expirou.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'waitlist.joined',
                'title' => 'Entrada na fila de espera',
                'category' => 'waitlist',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nVoce entrou na fila de espera de {{clinic.name}}.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'waitlist.offered',
                'title' => 'Oferta de vaga na fila de espera',
                'category' => 'waitlist',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Ola {{patient.name}}!\n\nUma vaga foi liberada em {{clinic.name}}.\nData: {{appointment.date}}\nHorario: {{appointment.time}}\nProfissional: {{professional.name}}\nConfirmar ate: {{waitlist.offer_expires_at}}\nLink: {{links.waitlist_offer}}",
                'variables' => [
                    'patient.name',
                    'clinic.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                    'waitlist.offer_expires_at',
                    'links.waitlist_offer',
                ],
                'is_active' => true,
            ],
        ];
    }
}
