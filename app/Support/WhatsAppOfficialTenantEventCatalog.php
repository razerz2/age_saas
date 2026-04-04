<?php

namespace App\Support;

class WhatsAppOfficialTenantEventCatalog
{
    /**
     * @return array<int, array{key: string, label: string, domain: string}>
     */
    public static function all(): array
    {
        $events = [
            ['key' => 'appointment.pending_confirmation', 'label' => 'Agendamento pendente de confirmacao', 'domain' => 'tenant'],
            ['key' => 'appointment.confirmed', 'label' => 'Agendamento confirmado', 'domain' => 'tenant'],
            ['key' => 'appointment.canceled', 'label' => 'Agendamento cancelado', 'domain' => 'tenant'],
            ['key' => 'appointment.expired', 'label' => 'Agendamento expirado', 'domain' => 'tenant'],
            ['key' => 'appointment.form_requested.patient', 'label' => 'Solicitacao de formulario para paciente', 'domain' => 'tenant'],
            ['key' => 'appointment.created.doctor', 'label' => 'Novo agendamento para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'appointment.confirmed.doctor', 'label' => 'Agendamento confirmado para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'appointment.canceled.doctor', 'label' => 'Agendamento cancelado para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'appointment.rescheduled.doctor', 'label' => 'Agendamento remarcado para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'waitlist.joined', 'label' => 'Entrada na fila de espera', 'domain' => 'tenant'],
            ['key' => 'waitlist.offered', 'label' => 'Oferta de vaga na fila de espera', 'domain' => 'tenant'],
            ['key' => 'waitlist.offered.doctor', 'label' => 'Oferta de vaga para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'waitlist.accepted.doctor', 'label' => 'Oferta aceita na fila de espera para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'form.response_submitted.doctor', 'label' => 'Resposta de formulario para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'online_appointment.updated.doctor', 'label' => 'Consulta online atualizada para {{labels.professional_singular_lower}}', 'domain' => 'tenant'],
            ['key' => 'online_appointment.instructions_sent.doctor', 'label' => 'Instrucoes da consulta online enviadas', 'domain' => 'tenant'],
            ['key' => 'online_appointment.form_response_submitted.doctor', 'label' => 'Resposta de formulario da consulta online', 'domain' => 'tenant'],

            ['key' => 'invoice.created', 'label' => 'Fatura criada', 'domain' => 'platform'],
            ['key' => 'invoice.upcoming_due', 'label' => 'Lembrete de fatura a vencer', 'domain' => 'platform'],
            ['key' => 'invoice.overdue', 'label' => 'Fatura vencida', 'domain' => 'platform'],
            ['key' => 'tenant.suspended_due_to_overdue', 'label' => 'Tenant suspenso por inadimplencia', 'domain' => 'platform'],
            ['key' => 'tenant.welcome', 'label' => 'Boas-vindas ao tenant', 'domain' => 'platform'],
            ['key' => 'subscription.created', 'label' => 'Assinatura criada', 'domain' => 'platform'],
            ['key' => 'trial.ends_in_7_days', 'label' => 'Trial termina em 7 dias', 'domain' => 'platform'],
            ['key' => 'trial.ends_in_3_days', 'label' => 'Trial termina em 3 dias', 'domain' => 'platform'],
            ['key' => 'trial.ends_today', 'label' => 'Trial termina hoje', 'domain' => 'platform'],
            ['key' => 'trial.expired', 'label' => 'Trial expirado', 'domain' => 'platform'],
            ['key' => 'subscription.recovery_started', 'label' => 'Recovery de assinatura iniciado', 'domain' => 'platform'],
            ['key' => 'credentials.resent', 'label' => 'Reenvio de credenciais', 'domain' => 'platform'],
            ['key' => 'security.2fa_code', 'label' => 'Codigo de verificacao (2FA)', 'domain' => 'platform'],
        ];

        return array_map(function (array $item): array {
            $item['label'] = strtr((string) ($item['label'] ?? ''), self::professionalLabelReplacements());

            return $item;
        }, $events);
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_values(array_map(
            static fn (array $item): string => (string) $item['key'],
            self::all()
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function labelsByKey(): array
    {
        $labels = [];
        foreach (self::all() as $item) {
            $labels[(string) $item['key']] = (string) $item['label'];
        }

        return $labels;
    }

    /**
     * @return array<string, array<int, array{key: string, label: string, domain: string}>>
     */
    public static function groupedByDomain(): array
    {
        $groups = [
            'tenant' => [],
            'platform' => [],
        ];

        foreach (self::all() as $item) {
            $domain = (string) ($item['domain'] ?? 'platform');
            if (!array_key_exists($domain, $groups)) {
                $groups[$domain] = [];
            }

            $groups[$domain][] = $item;
        }

        return $groups;
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
