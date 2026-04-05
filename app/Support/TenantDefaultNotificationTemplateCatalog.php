<?php

namespace App\Support;

class TenantDefaultNotificationTemplateCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $templates = [
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.pending_confirmation',
                'title' => 'Agendamento pendente de confirmação',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Olá {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} está pendente de confirmação.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}\nModalidade: {{appointment.mode}}\n\nConfirmar: {{links.appointment_confirm}}\nCancelar: {{links.appointment_cancel}}",
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
                'content' => "Olá {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} foi confirmado.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}",
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
                'content' => "Olá {{patient.name}}!\n\nSeu agendamento em {{clinic.name}} foi cancelado.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}",
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
                'content' => "Olá {{patient.name}}!\n\nO prazo para confirmar seu agendamento em {{clinic.name}} expirou.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}",
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
                'key' => 'appointment.form_requested.patient',
                'title' => 'Solicitação de formulário para paciente',
                'category' => 'appointment',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Olá {{patient.name}}!\n\nSeu formulário pré-consulta está disponível.\nFormulário: {{form.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}\n\nPreencher formulário: {{links.form_fill}}",
                'variables' => [
                    'patient.name',
                    'form.name',
                    'appointment.date',
                    'appointment.time',
                    'professional.name',
                    'links.form_fill',
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
                'content' => "Olá {{patient.name}}!\n\nVocê entrou na fila de espera de {{clinic.name}}.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}",
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
                'content' => "Olá {{patient.name}}!\n\nUma vaga foi liberada em {{clinic.name}}.\nData: {{appointment.date}}\nHorário: {{appointment.time}}\n{{labels.professional_singular}}: {{professional.name}}\nConfirmar até: {{waitlist.offer_expires_at}}\nLink: {{links.waitlist_offer}}",
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
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.created.doctor',
                'title' => 'Novo agendamento para {{labels.professional_singular_lower}}',
                'category' => 'appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Novo agendamento na sua agenda.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\nStatus: {{appointment.status}}\nDetalhes: {{links.appointment_details}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'appointment.mode',
                    'appointment.status',
                    'links.appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.confirmed.doctor',
                'title' => 'Agendamento confirmado para {{labels.professional_singular_lower}}',
                'category' => 'appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Agendamento confirmado na sua agenda.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\nDetalhes: {{links.appointment_details}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'appointment.mode',
                    'links.appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.canceled.doctor',
                'title' => 'Agendamento cancelado para {{labels.professional_singular_lower}}',
                'category' => 'appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Agendamento cancelado na sua agenda.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nStatus atual: {{appointment.status}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'appointment.status',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'appointment.rescheduled.doctor',
                'title' => 'Agendamento remarcado para {{labels.professional_singular_lower}}',
                'category' => 'appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Agendamento remarcado na sua agenda.\nPaciente: {{patient.name}}\nNova data: {{appointment.date}}\nNovo horário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\nDetalhes: {{links.appointment_details}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'appointment.mode',
                    'links.appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'waitlist.offered.doctor',
                'title' => 'Oferta de waitlist para {{labels.professional_singular_lower}}',
                'category' => 'waitlist_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Oferta de vaga enviada ao paciente.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nValidade: {{waitlist.offer_expires_at}}\nLink enviado: {{links.waitlist_offer}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'waitlist.offer_expires_at',
                    'links.waitlist_offer',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'waitlist.accepted.doctor',
                'title' => 'Oferta de waitlist aceita',
                'category' => 'waitlist_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Paciente aceitou oferta da fila de espera.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nStatus: {{appointment.status}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'doctor.specialty',
                    'appointment.status',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'form.response_submitted.doctor',
                'title' => 'Resposta de formulário para {{labels.professional_singular_lower}}',
                'category' => 'form_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Nova resposta de formulário recebida.\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nEnviado em: {{response.submitted_at}}\nAbrir resposta: {{links.form_response}}",
                'variables' => [
                    'patient.name',
                    'form.name',
                    'response.submitted_at',
                    'links.form_response',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'online_appointment.updated.doctor',
                'title' => 'Consulta online atualizada para {{labels.professional_singular_lower}}',
                'category' => 'online_appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Consulta online atualizada.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nModalidade: {{appointment.mode}}\nAplicativo: {{online.meeting_app}}\nLink da reunião: {{online.meeting_link}}\nDetalhes: {{links.online_appointment_details}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'appointment.mode',
                    'online.meeting_app',
                    'online.meeting_link',
                    'links.online_appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'online_appointment.instructions_sent.doctor',
                'title' => 'Instruções da consulta online enviadas',
                'category' => 'online_appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Instruções da consulta online enviadas ao paciente.\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nStatus das instruções: {{online.instructions_sent}}\nÚltimo envio por e-mail: {{online.instructions_sent_email_at}}\nÚltimo envio por WhatsApp: {{online.instructions_sent_whatsapp_at}}\nDetalhes: {{links.online_appointment_details}}",
                'variables' => [
                    'patient.name',
                    'appointment.date',
                    'appointment.time',
                    'online.instructions_sent',
                    'online.instructions_sent_email_at',
                    'online.instructions_sent_whatsapp_at',
                    'links.online_appointment_details',
                ],
                'is_active' => true,
            ],
            [
                'channel' => 'whatsapp',
                'key' => 'online_appointment.form_response_submitted.doctor',
                'title' => 'Resposta de formulário da consulta online',
                'category' => 'online_appointment_doctor',
                'language' => 'pt_BR',
                'subject' => null,
                'content' => "Nova resposta de formulário da consulta online.\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nData/Hora da consulta: {{appointment.datetime}}\nEnviado em: {{response.submitted_at}}\nAbrir resposta: {{links.form_response}}\nDetalhes da consulta online: {{links.online_appointment_details}}",
                'variables' => [
                    'patient.name',
                    'form.name',
                    'appointment.datetime',
                    'response.submitted_at',
                    'links.form_response',
                    'links.online_appointment_details',
                ],
                'is_active' => true,
            ],
        ];

        return array_map(function (array $template): array {
            $template['title'] = strtr((string) ($template['title'] ?? ''), self::professionalLabelReplacements());

            return $template;
        }, $templates);
    }

    /**
     * @return array<string, string>
     */
    private static function professionalLabelReplacements(): array
    {
        $singular = 'Médico';
        $plural = 'Médicos';
        $registration = 'CRM';

        try {
            /** @var \App\Services\Tenant\ProfessionalLabelService $service */
            $service = app(\App\Services\Tenant\ProfessionalLabelService::class);
            $resolvedSingular = trim((string) $service->singular());
            $resolvedPlural = trim((string) $service->plural());
            $resolvedRegistration = trim((string) $service->registration());

            if ($resolvedSingular !== '') {
                $singular = $resolvedSingular;
            }
            if ($resolvedPlural !== '') {
                $plural = $resolvedPlural;
            }
            if ($resolvedRegistration !== '') {
                $registration = $resolvedRegistration;
            }
        } catch (\Throwable) {
            // Keep default labels when tenant context is unavailable.
        }

        return [
            '{{labels.professional_singular}}' => $singular,
            '{{labels.professional_plural}}' => $plural,
            '{{labels.professional_registration}}' => $registration,
            '{{labels.professional_singular_lower}}' => self::toLower($singular),
            '{{labels.professional_plural_lower}}' => self::toLower($plural),
            '{{labels.professional_registration_lower}}' => self::toLower($registration),
        ];
    }

    private static function toLower(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
