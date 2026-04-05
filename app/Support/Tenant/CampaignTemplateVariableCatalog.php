<?php

namespace App\Support\Tenant;

class CampaignTemplateVariableCatalog
{
    /**
     * @return array<string, array<int, array{key:string,description:string}>>
     */
    public function all(): array
    {
        return [
            'CLÍNICA' => [
                ['key' => '{{clinic.name}}', 'description' => 'Nome da clínica'],
                ['key' => '{{clinic.phone}}', 'description' => 'Telefone da clínica'],
                ['key' => '{{clinic.email}}', 'description' => 'E-mail da clínica'],
                ['key' => '{{clinic.address}}', 'description' => 'Endereço da clínica'],
            ],
            'PACIENTE / CONTATO' => [
                ['key' => '{{patient.name}}', 'description' => 'Nome do paciente'],
                ['key' => '{{patient.phone}}', 'description' => 'Telefone do paciente'],
                ['key' => '{{patient.email}}', 'description' => 'E-mail do paciente'],
            ],
            'LINKS' => [
                ['key' => '{{links.public_booking}}', 'description' => 'Link para o agendamento público da clínica'],
                ['key' => '{{links.portal}}', 'description' => 'Link do portal do cliente (se existir)'],
                ['key' => '{{links.whatsapp}}', 'description' => 'Link de WhatsApp da clínica (se existir)'],
            ],
        ];
    }
}

