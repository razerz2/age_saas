<?php

namespace Database\Seeders;

use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Database\Seeder;

class WhatsAppOfficialTenantTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'appointment.pending_confirmation',
                'meta_template_name' => 'tenant_appointment_pending_confirmation',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nSeu agendamento em {{2}} está pendente de confirmação.\nData: {{3}}.\nHorário: {{4}}.\nProfissional: {{5}}.\n\nConfirme no link: {{6}}.\nSe precisar cancelar, use: {{7}}.\n\nEm caso de dúvidas, responda esta mensagem.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                    '6' => 'appointment_confirm_link',
                    '7' => 'appointment_cancel_link',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                    '6' => 'https://app.exemplo.com/t/clinica-vida/agendamento/confirmar/abc123',
                    '7' => 'https://app.exemplo.com/t/clinica-vida/agendamento/cancelar/abc123',
                ],
            ],
            [
                'key' => 'appointment.confirmed',
                'meta_template_name' => 'tenant_appointment_confirmed',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nSeu agendamento em {{2}} foi confirmado.\nData: {{3}}.\nHorário: {{4}}.\nProfissional: {{5}}.\n\nDetalhes: {{6}}.\n\nSe tiver dúvidas, fale com a clínica.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                    '6' => 'appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                    '6' => 'https://app.exemplo.com/t/clinica-vida/agendamento/detalhes/abc123',
                ],
            ],
            [
                'key' => 'appointment.canceled',
                'meta_template_name' => 'tenant_appointment_canceled',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nSeu agendamento em {{2}} foi cancelado.\nData: {{3}}.\nHorário: {{4}}.\nProfissional: {{5}}.\n\nSe desejar, solicite um novo horário com a clínica.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                ],
            ],
            [
                'key' => 'appointment.expired',
                'meta_template_name' => 'tenant_appointment_expired',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nO prazo para confirmar seu agendamento em {{2}} expirou.\nData: {{3}}.\nHorário: {{4}}.\nProfissional: {{5}}.\n\nSe necessário, solicite um novo agendamento.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                ],
            ],
            [
                'key' => 'waitlist.joined',
                'meta_template_name' => 'tenant_waitlist_joined',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nVocê entrou na fila de espera de {{2}}.\nData de referência: {{3}}.\nHorário de referência: {{4}}.\nProfissional: {{5}}.\n\nQuando surgir vaga, enviaremos uma nova mensagem.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                ],
            ],
            [
                'key' => 'waitlist.offered',
                'meta_template_name' => 'tenant_waitlist_offered',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nUma vaga foi liberada em {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nProfissional: {{5}}.\nConfirme até: {{6}}.\n\nUse este link para confirmar: {{7}}.\n\nSe não puder comparecer, desconsidere esta mensagem.",
                'variables' => [
                    '1' => 'patient_name',
                    '2' => 'clinic_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'professional_name',
                    '6' => 'waitlist_offer_expires_at',
                    '7' => 'waitlist_offer_link',
                ],
                'sample_variables' => [
                    '1' => 'Ana Souza',
                    '2' => 'Clínica Vida',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Dr. Carlos Lima',
                    '6' => '18/03/2026 12:00',
                    '7' => 'https://app.exemplo.com/t/clinica-vida/lista-espera/oferta/abc123',
                ],
            ],
            [
                'key' => 'appointment.created.doctor',
                'meta_template_name' => 'tenant_appointment_created_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nNovo agendamento na sua agenda.\nPaciente: {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nModalidade: {{5}}.\nStatus: {{6}}.\n\nDetalhes: {{7}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'appointment_mode',
                    '6' => 'appointment_status',
                    '7' => 'appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Presencial',
                    '6' => 'pending_confirmation',
                    '7' => 'https://app.exemplo.com/t/clinica-vida/responses/abc123',
                ],
            ],
            [
                'key' => 'appointment.confirmed.doctor',
                'meta_template_name' => 'tenant_appointment_confirmed_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nAgendamento confirmado na sua agenda.\nPaciente: {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nModalidade: {{5}}.\n\nDetalhes: {{6}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'appointment_mode',
                    '6' => 'appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'Presencial',
                    '6' => 'https://app.exemplo.com/t/clinica-vida/responses/abc123',
                ],
            ],
            [
                'key' => 'appointment.canceled.doctor',
                'meta_template_name' => 'tenant_appointment_canceled_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nAgendamento cancelado na sua agenda.\nPaciente: {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nStatus: {{5}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'appointment_status',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'canceled',
                ],
            ],
            [
                'key' => 'appointment.rescheduled.doctor',
                'meta_template_name' => 'tenant_appointment_rescheduled_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nAgendamento remarcado na sua agenda.\nPaciente: {{2}}.\nNova data: {{3}}.\nNovo horário: {{4}}.\nModalidade: {{5}}.\n\nDetalhes: {{6}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'appointment_mode',
                    '6' => 'appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '19/03/2026',
                    '4' => '10:00',
                    '5' => 'Online',
                    '6' => 'https://app.exemplo.com/t/clinica-vida/responses/abc123',
                ],
            ],
            [
                'key' => 'waitlist.offered.doctor',
                'meta_template_name' => 'tenant_waitlist_offered_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nOferta de vaga enviada ao paciente {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nValidade: {{5}}.\nLink enviado: {{6}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'waitlist_offer_expires_at',
                    '6' => 'waitlist_offer_link',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => '18/03/2026 12:00',
                    '6' => 'https://app.exemplo.com/t/clinica-vida/lista-espera/oferta/abc123',
                ],
            ],
            [
                'key' => 'waitlist.accepted.doctor',
                'meta_template_name' => 'tenant_waitlist_accepted_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nPaciente {{2}} aceitou oferta da fila de espera.\nData: {{3}}.\nHorário: {{4}}.\nStatus do agendamento: {{5}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'appointment_status',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => '18/03/2026',
                    '4' => '14:30',
                    '5' => 'scheduled',
                ],
            ],
            [
                'key' => 'form.response_submitted.doctor',
                'meta_template_name' => 'tenant_form_response_submitted_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nNova resposta de formulário recebida.\nPaciente: {{2}}.\nFormulário: {{3}}.\nEnviado em: {{4}}.\n\nAbrir resposta: {{5}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'form_name',
                    '4' => 'response_submitted_at',
                    '5' => 'form_response_link',
                ],
                'sample_variables' => [
                    '1' => 'Dr. Carlos Lima',
                    '2' => 'Ana Souza',
                    '3' => 'Pré-consulta',
                    '4' => '18/03/2026 11:15',
                    '5' => 'https://app.exemplo.com/t/clinica-vida/responses/abc123',
                ],
            ],
            [
                'key' => 'online_appointment.updated.doctor',
                'meta_template_name' => 'tenant_online_appointment_updated_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nConsulta online atualizada.\nPaciente: {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nAplicativo: {{5}}.\nLink da reunião: {{6}}.\n\nDetalhes: {{7}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'meeting_app',
                    '6' => 'meeting_link',
                    '7' => 'online_appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dra. Fernanda Lima',
                    '2' => 'Ana Souza',
                    '3' => '22/03/2026',
                    '4' => '16:00',
                    '5' => 'Google Meet',
                    '6' => 'https://meet.google.com/abc-defg-hij',
                    '7' => 'https://app.exemplo.com/customer/clinica-vida/consultas-online/abc123',
                ],
            ],
            [
                'key' => 'online_appointment.instructions_sent.doctor',
                'meta_template_name' => 'tenant_online_appointment_instructions_sent_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nAs instruções da consulta online foram enviadas ao paciente {{2}}.\nData: {{3}}.\nHorário: {{4}}.\nÚltimo envio por e-mail: {{5}}.\nÚltimo envio por WhatsApp: {{6}}.\n\nDetalhes: {{7}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'appointment_date',
                    '4' => 'appointment_time',
                    '5' => 'instructions_sent_email_at',
                    '6' => 'instructions_sent_whatsapp_at',
                    '7' => 'online_appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dra. Fernanda Lima',
                    '2' => 'Ana Souza',
                    '3' => '22/03/2026',
                    '4' => '16:00',
                    '5' => '22/03/2026 10:30',
                    '6' => '22/03/2026 10:31',
                    '7' => 'https://app.exemplo.com/customer/clinica-vida/consultas-online/abc123',
                ],
            ],
            [
                'key' => 'online_appointment.form_response_submitted.doctor',
                'meta_template_name' => 'tenant_online_appointment_form_response_submitted_doctor',
                'category' => 'UTILITY',
                'body_text' => "Olá {{1}}.\n\nO paciente {{2}} enviou a resposta do formulário da consulta online.\nFormulário: {{3}}.\nConsulta: {{4}}.\nResposta enviada em: {{5}}.\n\nAbrir resposta: {{6}}.\nDetalhes da consulta online: {{7}}.",
                'variables' => [
                    '1' => 'doctor_name',
                    '2' => 'patient_name',
                    '3' => 'form_name',
                    '4' => 'appointment_datetime',
                    '5' => 'response_submitted_at',
                    '6' => 'form_response_link',
                    '7' => 'online_appointment_details_link',
                ],
                'sample_variables' => [
                    '1' => 'Dra. Fernanda Lima',
                    '2' => 'Ana Souza',
                    '3' => 'Pré-consulta Online',
                    '4' => '22/03/2026 16:00',
                    '5' => '22/03/2026 14:42',
                    '6' => 'https://app.exemplo.com/customer/clinica-vida/responses/abc123',
                    '7' => 'https://app.exemplo.com/customer/clinica-vida/consultas-online/abc123',
                ],
            ],
        ];

        foreach ($templates as $payload) {
            $template = WhatsAppOfficialTemplate::query()->firstOrNew([
                'provider' => WhatsAppOfficialTemplate::PROVIDER,
                'key' => $payload['key'],
                'version' => 1,
            ]);

            $isNew = !$template->exists;

            $template->fill([
                'meta_template_name' => $payload['meta_template_name'],
                'provider' => WhatsAppOfficialTemplate::PROVIDER,
                'category' => $payload['category'],
                'language' => 'pt_BR',
                'header_text' => null,
                'body_text' => $payload['body_text'],
                'footer_text' => null,
                'buttons' => null,
                'variables' => $payload['variables'],
                'sample_variables' => $payload['sample_variables'],
            ]);

            if ($isNew) {
                // Novo baseline entra como draft; aprovacao real depende da Meta.
                $template->status = WhatsAppOfficialTemplate::STATUS_DRAFT;
            } elseif (trim((string) $template->status) === '') {
                $template->status = WhatsAppOfficialTemplate::STATUS_DRAFT;
            }

            $template->save();
        }
    }
}
