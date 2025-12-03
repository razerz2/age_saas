<?php

namespace Database\Seeders;

use App\Models\Platform\NotificationTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // EMAIL TEMPLATES
            [
                'name' => 'subscription_created',
                'display_name' => 'Assinatura Criada',
                'channel' => 'email',
                'default_subject' => 'Assinatura criada com sucesso!',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Sua assinatura foi criada com sucesso.</p><p>Plano: {{plan_name}}</p><p>Valor: R$ {{plan_value}}</p>',
                'variables' => ['tenant_name', 'plan_name', 'plan_value'],
            ],
            [
                'name' => 'subscription_renewed',
                'display_name' => 'Assinatura Renovada',
                'channel' => 'email',
                'default_subject' => 'Sua assinatura foi renovada',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Sua assinatura foi renovada com sucesso.</p><p>Próximo vencimento: {{next_due_date}}</p>',
                'variables' => ['tenant_name', 'next_due_date'],
            ],
            [
                'name' => 'invoice_created',
                'display_name' => 'Fatura Criada',
                'channel' => 'email',
                'default_subject' => 'Nova fatura disponível - {{invoice_value}}',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Uma nova fatura foi gerada para você.</p><p>Valor: R$ {{invoice_value}}</p><p>Vencimento: {{due_date}}</p><p><a href="{{payment_url}}">Clique aqui para pagar</a></p>',
                'variables' => ['tenant_name', 'invoice_value', 'due_date', 'payment_url'],
            ],
            [
                'name' => 'invoice_paid',
                'display_name' => 'Fatura Paga',
                'channel' => 'email',
                'default_subject' => 'Pagamento confirmado - Fatura #{{invoice_id}}',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Seu pagamento foi confirmado com sucesso!</p><p>Fatura: #{{invoice_id}}</p><p>Valor pago: R$ {{invoice_value}}</p><p>Data do pagamento: {{payment_date}}</p>',
                'variables' => ['tenant_name', 'invoice_id', 'invoice_value', 'payment_date'],
            ],
            [
                'name' => 'invoice_overdue',
                'display_name' => 'Fatura Vencida',
                'channel' => 'email',
                'default_subject' => 'Atenção: Fatura vencida - {{invoice_value}}',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Sua fatura está vencida.</p><p>Valor: R$ {{invoice_value}}</p><p>Vencimento: {{due_date}}</p><p><a href="{{payment_url}}">Clique aqui para pagar</a></p>',
                'variables' => ['tenant_name', 'invoice_value', 'due_date', 'payment_url'],
            ],
            [
                'name' => 'tenant_welcome',
                'display_name' => 'Bem-vindo ao Sistema (com credenciais)',
                'channel' => 'email',
                'default_subject' => 'Bem-vindo ao sistema! Suas credenciais de acesso',
                'default_body' => '<h1>Olá {{tenant_name}}!</h1><p>Bem-vindo ao nosso sistema!</p><p>Suas credenciais de acesso:</p><p><strong>Email:</strong> {{email}}</p><p><strong>Senha:</strong> {{password}}</p><p><a href="{{login_url}}">Acessar sistema</a></p>',
                'variables' => ['tenant_name', 'email', 'password', 'login_url'],
            ],
            [
                'name' => 'pre_tenant_created',
                'display_name' => 'Pré-Cadastro Criado',
                'channel' => 'email',
                'default_subject' => 'Pré-cadastro recebido com sucesso!',
                'default_body' => '<h1>Olá {{pre_tenant_name}}!</h1><p>Seu pré-cadastro foi recebido e está em análise.</p><p>Em breve entraremos em contato.</p>',
                'variables' => ['pre_tenant_name'],
            ],
            [
                'name' => 'pre_tenant_payment_confirmed',
                'display_name' => 'Pagamento Pré-Cadastro Confirmado',
                'channel' => 'email',
                'default_subject' => 'Pagamento confirmado - Seu cadastro será processado',
                'default_body' => '<h1>Olá {{pre_tenant_name}}!</h1><p>Seu pagamento foi confirmado!</p><p>Estamos processando seu cadastro e em breve você receberá suas credenciais de acesso.</p>',
                'variables' => ['pre_tenant_name'],
            ],
            // WHATSAPP TEMPLATES
            [
                'name' => 'invoice_notification',
                'display_name' => 'Notificação de Fatura',
                'channel' => 'whatsapp',
                'default_subject' => null,
                'default_body' => '💰 *Nova fatura disponível!*\n\nCliente: {{tenant_name}}\nValor: R$ {{invoice_value}}\nVencimento: {{due_date}}\n\n💳 Link para pagamento:\n{{payment_url}}\n\nAgradecemos pela preferência 🙏',
                'variables' => ['tenant_name', 'invoice_value', 'due_date', 'payment_url'],
            ],
            [
                'name' => 'welcome_short',
                'display_name' => 'Bem-vindo (Curto)',
                'channel' => 'whatsapp',
                'default_subject' => null,
                'default_body' => 'Olá {{tenant_name}}! 👋\n\nBem-vindo ao nosso sistema!\n\nSeu acesso:\nEmail: {{email}}\nSenha: {{password}}\n\nAcesse: {{login_url}}',
                'variables' => ['tenant_name', 'email', 'password', 'login_url'],
            ],
            [
                'name' => 'subscription_alert',
                'display_name' => 'Alerta de Assinatura',
                'channel' => 'whatsapp',
                'default_subject' => null,
                'default_body' => '⚠️ *Alerta de Assinatura*\n\n{{tenant_name}}, sua assinatura {{action}}.\n\nPlano: {{plan_name}}\n{{additional_info}}',
                'variables' => ['tenant_name', 'action', 'plan_name', 'additional_info'],
            ],
        ];

        foreach ($templates as $templateData) {
            $existing = NotificationTemplate::where('name', $templateData['name'])->first();
            
            if ($existing) {
                // Atualizar registro existente
                $existing->update([
                    'display_name' => $templateData['display_name'],
                    'channel' => $templateData['channel'],
                    'default_subject' => $templateData['default_subject'],
                    'default_body' => $templateData['default_body'],
                    'variables' => $templateData['variables'],
                    // Manter subject e body customizados se existirem, senão usar padrão
                    'subject' => $existing->subject ?: $templateData['default_subject'],
                    'body' => $existing->body ?: $templateData['default_body'],
                ]);
            } else {
                // Criar novo registro
                NotificationTemplate::create(array_merge($templateData, [
                    'id' => (string) Str::uuid(),
                    'subject' => $templateData['default_subject'],
                    'body' => $templateData['default_body'],
                    'enabled' => true,
                ]));
            }
        }
    }
}
