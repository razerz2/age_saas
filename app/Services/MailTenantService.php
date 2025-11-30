<?php

namespace App\Services;

use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailTenantService
{
    /**
     * Envia email usando SMTP do tenant ou global
     */
    public static function send($to, $subject, $view, $data = [])
    {
        try {
            $provider = TenantSetting::emailProvider();

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

            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
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

