<?php

namespace App\Services;

use App\Models\Platform\SystemSetting;

class SystemSettingsService
{
    /**
     * Verifica se o serviço de email está configurado corretamente.
     * 
     * @return bool
     */
    public function emailIsConfigured(): bool
    {
        $settings = $this->getEmailSettings();

        return !empty($settings['smtp_host'])
            && !empty($settings['smtp_port'])
            && !empty($settings['smtp_username'])
            && !empty($settings['smtp_password'])
            && !empty($settings['smtp_from_address']);
    }

    /**
     * Retorna as configurações de email do sistema.
     * 
     * @return array
     */
    public function getEmailSettings(): array
    {
        return [
            'smtp_host' => sysconfig('MAIL_HOST', env('MAIL_HOST')),
            'smtp_port' => sysconfig('MAIL_PORT', env('MAIL_PORT')),
            'smtp_username' => sysconfig('MAIL_USERNAME', env('MAIL_USERNAME')),
            'smtp_password' => sysconfig('MAIL_PASSWORD', env('MAIL_PASSWORD')),
            'smtp_from_address' => sysconfig('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
            'smtp_from_name' => sysconfig('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Plataforma')),
        ];
    }
}

