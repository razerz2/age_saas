<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class WhatsAppUnofficialTemplateFakeDataFactory
{
    /**
     * @param  list<string>  $variables
     * @return array<string, string>
     */
    public function build(array $variables): array
    {
        $result = [];

        foreach ($variables as $variable) {
            $name = trim((string) $variable);
            if ($name === '') {
                continue;
            }

            $result[$name] = $this->valueFor($name);
        }

        return $result;
    }

    public function valueFor(string $variable): string
    {
        $key = strtolower(trim($variable));
        $date = Carbon::now()->addDays(7)->format('d/m/Y');
        $time = Carbon::now()->addHour()->format('H:i');

        return match ($key) {
            'customer_name', 'patient.name' => 'Rafael Souza',
            'tenant_name', 'clinic.name' => 'Clínica Boa Vida',
            'invoice_amount', 'plan_amount' => 'R$ 249,90',
            'due_date', 'appointment.date', 'appointment_date' => $date,
            'appointment.time', 'appointment_time' => $time,
            'payment_link', 'payment_url' => 'https://app.allsync.com.br/faturas/pagar/teste-123',
            'login_url' => 'https://app.allsync.com.br/Platform/login',
            'links.waitlist_offer' => 'https://app.allsync.com.br/public/waitlist/oferta/teste-123',
            'links.appointment_confirm' => 'https://app.allsync.com.br/public/agendamento/confirmar/teste-123',
            'links.appointment_cancel' => 'https://app.allsync.com.br/public/agendamento/cancelar/teste-123',
            'links.appointment_details' => 'https://app.allsync.com.br/public/agendamento/detalhe/teste-123',
            'code' => '123456',
            'expires_in_minutes' => '10',
            default => $this->fallbackValue($variable),
        };
    }

    private function fallbackValue(string $variable): string
    {
        $normalized = strtolower(trim($variable));
        $normalized = str_replace(['.', '-', ' '], '_', $normalized);
        $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized) ?? '';
        if ($normalized === '') {
            $normalized = 'variavel';
        }

        return 'valor_teste_' . $normalized;
    }
}

