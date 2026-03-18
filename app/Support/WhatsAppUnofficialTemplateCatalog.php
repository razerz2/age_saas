<?php

namespace App\Support;

class WhatsAppUnofficialTemplateCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'invoice.created',
                'title' => 'Fatura criada',
                'category' => 'billing',
                'body' => "Ola {{customer_name}}!\n\nUma nova fatura foi gerada para {{tenant_name}}.\nValor: {{invoice_amount}}\nVencimento: {{due_date}}\nLink de pagamento: {{payment_link}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'invoice_amount',
                    'due_date',
                    'payment_link',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'invoice.upcoming_due',
                'title' => 'Lembrete de vencimento',
                'category' => 'billing',
                'body' => "Ola {{customer_name}}!\n\nLembrete de vencimento para {{tenant_name}}.\nValor: {{invoice_amount}}\nVencimento: {{due_date}}\nLink de pagamento: {{payment_link}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'invoice_amount',
                    'due_date',
                    'payment_link',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'invoice.overdue',
                'title' => 'Fatura vencida',
                'category' => 'billing',
                'body' => "Ola {{customer_name}}!\n\nIdentificamos fatura vencida para {{tenant_name}}.\nValor: {{invoice_amount}}\nVencimento: {{due_date}}\nRegularize pelo link: {{payment_link}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'invoice_amount',
                    'due_date',
                    'payment_link',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'tenant.suspended_due_to_overdue',
                'title' => 'Suspensao por inadimplencia',
                'category' => 'billing',
                'body' => "Ola {{customer_name}}!\n\nO tenant {{tenant_name}} foi suspenso por inadimplencia.\nValor pendente: {{invoice_amount}}\nVencimento: {{due_date}}\nLink para regularizacao: {{payment_link}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'invoice_amount',
                    'due_date',
                    'payment_link',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'tenant.welcome',
                'title' => 'Boas-vindas ao tenant',
                'category' => 'onboarding',
                'body' => "Ola {{customer_name}}!\n\nBem-vindo ao tenant {{tenant_name}}.\nSeu acesso inicial ja esta pronto.\nLogin: {{login_url}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'login_url',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'subscription.created',
                'title' => 'Assinatura criada',
                'category' => 'subscription',
                'body' => "Ola {{customer_name}}!\n\nA assinatura do tenant {{tenant_name}} foi criada.\nPlano: {{plan_name}}\nValor: {{plan_amount}}\nProximo vencimento: {{due_date}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'plan_name',
                    'plan_amount',
                    'due_date',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'subscription.recovery_started',
                'title' => 'Recuperacao de assinatura iniciada',
                'category' => 'subscription',
                'body' => "Ola {{customer_name}}!\n\nA assinatura do tenant {{tenant_name}} entrou em recuperacao.\nValor pendente: {{invoice_amount}}\nRegularize ate {{due_date}}: {{payment_link}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'invoice_amount',
                    'due_date',
                    'payment_link',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'credentials.resent',
                'title' => 'Credenciais reenviadas',
                'category' => 'access',
                'body' => "Ola {{customer_name}}!\n\nAs credenciais de acesso do tenant {{tenant_name}} foram reenviadas.\nLogin: {{login_url}}\nCanal: {{delivery_channel}}",
                'variables' => [
                    'customer_name',
                    'tenant_name',
                    'login_url',
                    'delivery_channel',
                ],
                'is_active' => true,
            ],
            [
                'key' => 'security.2fa_code',
                'title' => 'Codigo de verificacao 2FA',
                'category' => 'security',
                'body' => "Ola {{customer_name}}!\n\nSeu codigo de verificacao e {{code}}.\nEste codigo expira em {{expires_in_minutes}} minutos.",
                'variables' => [
                    'customer_name',
                    'code',
                    'expires_in_minutes',
                ],
                'is_active' => true,
            ],
        ];
    }
}
