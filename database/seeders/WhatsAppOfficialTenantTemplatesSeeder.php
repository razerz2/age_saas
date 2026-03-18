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
