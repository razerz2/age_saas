<?php

namespace Database\Seeders;

use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Database\Seeder;

class WhatsAppOfficialTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'invoice.created',
                'meta_template_name' => 'saas_invoice_created',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nUma nova fatura foi gerada para {{2}}.\n\nValor: {{3}}.\nVencimento: {{4}}.\nLink de pagamento: {{5}}.\n\nSe precisar, fale com nosso suporte.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'invoice_amount',
                    '4' => 'due_date',
                    '5' => 'payment_link',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'R$ 299,90',
                    '4' => '25/03/2026',
                    '5' => 'https://app.allsync.com.br/faturas/pagar/abc123',
                ],
            ],
            [
                'key' => 'invoice.upcoming_due',
                'meta_template_name' => 'saas_invoice_upcoming_due',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nLembrete: a fatura de {{2}} vence em {{3}}.\n\nValor: {{4}}.\nLink de pagamento: {{5}}.\n\nEvite bloqueio mantendo o pagamento em dia.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'due_date',
                    '4' => 'invoice_amount',
                    '5' => 'payment_link',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => '22/03/2026',
                    '4' => 'R$ 299,90',
                    '5' => 'https://app.allsync.com.br/faturas/pagar/abc123',
                ],
            ],
            [
                'key' => 'invoice.overdue',
                'meta_template_name' => 'saas_invoice_overdue',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nIdentificamos fatura vencida para {{2}}.\n\nValor: {{3}}.\nVencimento: {{4}}.\nLink para regularizacao: {{5}}.\n\nRegularize para evitar suspensao do tenant.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'invoice_amount',
                    '4' => 'due_date',
                    '5' => 'payment_link',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'R$ 299,90',
                    '4' => '10/03/2026',
                    '5' => 'https://app.allsync.com.br/faturas/pagar/abc123',
                ],
            ],
            [
                'key' => 'tenant.suspended_due_to_overdue',
                'meta_template_name' => 'saas_tenant_suspended_due_to_overdue',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nO tenant {{2}} foi suspenso por inadimplencia.\n\nFatura: {{3}}.\nVencimento: {{4}}.\nLink para pagamento: {{5}}.\n\nApos a regularizacao, o acesso sera reativado.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'invoice_amount',
                    '4' => 'due_date',
                    '5' => 'payment_link',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'R$ 299,90',
                    '4' => '10/03/2026',
                    '5' => 'https://app.allsync.com.br/faturas/pagar/abc123',
                ],
            ],
            [
                'key' => 'security.2fa_code',
                'meta_template_name' => 'saas_security_2fa_code',
                'category' => 'SECURITY',
                'body_text' => "Ola {{1}}.\n\nSeu codigo de verificacao e {{2}}.\n\nEste codigo expira em {{3}} minutos.\n\nSe voce nao solicitou este acesso, altere sua senha imediatamente.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'code',
                    '3' => 'expires_in_minutes',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => '123456',
                    '3' => '10',
                ],
            ],
            [
                'key' => 'tenant.welcome',
                'meta_template_name' => 'saas_tenant_welcome',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nBem-vindo ao tenant {{2}}.\n\nSeu acesso inicial esta pronto.\nLogin: {{3}}.\n\nGuarde este link para acessar a plataforma.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'login_url',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'https://app.allsync.com.br/platform/login',
                ],
            ],
            [
                'key' => 'subscription.created',
                'meta_template_name' => 'saas_subscription_created',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nA assinatura do tenant {{2}} foi criada com sucesso.\n\nPlano: {{3}}.\nValor do plano: {{4}}.\nProximo vencimento: {{5}}.\n\nObrigado por usar nossa plataforma.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'plan_name',
                    '4' => 'plan_amount',
                    '5' => 'due_date',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'Plano Premium',
                    '4' => 'R$ 299,90',
                    '5' => '25/03/2026',
                ],
            ],
            [
                'key' => 'subscription.recovery_started',
                'meta_template_name' => 'saas_subscription_recovery_started',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nA assinatura do tenant {{2}} entrou em recuperacao por inadimplencia.\n\nValor pendente: {{3}}.\nRegularize ate {{4}} no link: {{5}}.\n\nApos a confirmacao do pagamento, o acesso sera reativado.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'invoice_amount',
                    '4' => 'due_date',
                    '5' => 'payment_link',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'R$ 299,90',
                    '4' => '25/03/2026',
                    '5' => 'https://app.allsync.com.br/faturas/pagar/abc123',
                ],
            ],
            [
                'key' => 'credentials.resent',
                'meta_template_name' => 'saas_credentials_resent',
                'category' => 'UTILITY',
                'body_text' => "Ola {{1}}.\n\nAs credenciais de acesso do tenant {{2}} foram reenviadas.\n\nLogin: {{3}}.\nCanal de envio: {{4}}.\n\nSe nao recebeu, verifique spam ou solicite novo envio.",
                'variables' => [
                    '1' => 'customer_name',
                    '2' => 'tenant_name',
                    '3' => 'login_url',
                    '4' => 'delivery_channel',
                ],
                'sample_variables' => [
                    '1' => 'Rafael',
                    '2' => 'Clinica Exemplo',
                    '3' => 'https://app.allsync.com.br/platform/login',
                    '4' => 'email',
                ],
            ],
        ];

        foreach ($templates as $template) {
            WhatsAppOfficialTemplate::query()->updateOrCreate(
                [
                    'provider' => WhatsAppOfficialTemplate::PROVIDER,
                    'key' => $template['key'],
                    'version' => 1,
                ],
                [
                    'meta_template_name' => $template['meta_template_name'],
                    'category' => $template['category'],
                    'language' => 'pt_BR',
                    'header_text' => null,
                    'body_text' => $template['body_text'],
                    'footer_text' => null,
                    'buttons' => null,
                    'variables' => $template['variables'],
                    'sample_variables' => $template['sample_variables'],
                    'status' => WhatsAppOfficialTemplate::STATUS_DRAFT,
                ]
            );
        }
    }
}
