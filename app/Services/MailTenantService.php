<?php

namespace App\Services;

use App\Helpers\EmailLayoutHelper;
use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Services\FeatureAccessService;

class MailTenantService
{
    /**
     * Envia email usando SMTP do tenant ou global
     * Aplica automaticamente o layout de email configurado
     */
    public static function send($to, $subject, $view, $data = [])
    {
        try {
            // Verifica se a funcionalidade de notificação por email está habilitada no plano
            $featureAccess = new FeatureAccessService();
            if (!$featureAccess->hasFeature('email_notifications')) {
                Log::info('📧 Email não enviado: funcionalidade de notificação por email não está habilitada no plano', [
                    'to' => $to,
                    'subject' => $subject
                ]);
                return;
            }

            $provider = TenantSetting::emailProvider();
            $data = array_merge(['subject' => $subject], $data);

            if ($provider['driver'] === 'tenancy') {
                // Configura SMTP do tenant
                config([
                    'mail.mailers.tenant_smtp' => [
                        'transport' => 'smtp',
                        'host' => $provider['host'],
                        'port' => $provider['port'],
                        'username' => $provider['username'],
                        'password' => $provider['password'],
                        'encryption' => null,
                    ],
                    'mail.default' => 'tenant_smtp',
                    'mail.from.address' => $provider['from_address'],
                    'mail.from.name' => $provider['from_name'],
                ]);
            }
            // Se driver for 'global', usa configuração padrão do Laravel

            // Renderiza a view e aplica o layout (ou usa conteúdo bruto se não for view)
            if (is_string($view) && View::exists($view)) {
                $html = EmailLayoutHelper::renderViewContent($view, $data);
            } else {
                $rawContent = is_string($view) ? $view : '';
                $hasHtml = $rawContent !== strip_tags($rawContent);
                $content = $hasHtml ? $rawContent : nl2br(e($rawContent));
                $html = EmailLayoutHelper::apply($content, $data);

                Log::info('📧 MailTenantService usando conteúdo bruto', [
                    'to' => $to,
                    'subject' => $subject,
                    'driver' => $provider['driver'],
                ]);
            }

            Mail::send([], [], function ($message) use ($to, $subject, $html) {
                $message->to($to)
                    ->subject($subject)
                    ->html($html);
            });

            Log::info('📧 Email enviado', ['to' => $to, 'subject' => $subject, 'driver' => $provider['driver']]);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

