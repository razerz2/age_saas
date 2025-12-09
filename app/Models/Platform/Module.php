<?php

namespace App\Models\Platform;

class Module
{
    /**
     * Retorna a lista de módulos disponíveis no sistema.
     * Cada módulo tem uma chave (key), nome e ícone opcional.
     */
    public static function all(): array
    {
        return [
            ['key' => 'tenants', 'name' => 'Tenants', 'icon' => 'fa-building'],
            ['key' => 'pre_tenants', 'name' => 'Pré-Cadastros', 'icon' => 'fa-user-plus'],
            ['key' => 'plans', 'name' => 'Planos', 'icon' => 'fa-box'],
            ['key' => 'subscriptions', 'name' => 'Assinaturas', 'icon' => 'fa-receipt'],
            ['key' => 'invoices', 'name' => 'Faturas', 'icon' => 'fa-file-invoice-dollar'],
            ['key' => 'medical_specialties_catalog', 'name' => 'Catálogo Médico', 'icon' => 'fa-stethoscope'],
            ['key' => 'notifications_outbox', 'name' => 'Notificações', 'icon' => 'fa-bell'],
            ['key' => 'system_notifications', 'name' => 'Notificações do Sistema', 'icon' => 'fa-bell-slash'],
            ['key' => 'notification_templates', 'name' => 'Templates de Notificação', 'icon' => 'fa-envelope'],
            ['key' => 'locations', 'name' => 'Localização', 'icon' => 'fa-globe'],
            ['key' => 'users', 'name' => 'Usuários', 'icon' => 'fa-users'],
            ['key' => 'settings', 'name' => 'Configurações', 'icon' => 'fa-cog'],
            ['key' => 'api_tokens', 'name' => 'Tokens de API', 'icon' => 'fa-key'],
        ];
    }

    /**
     * Retorna o nome amigável de um módulo a partir da key.
     */
    public static function getName(string $key): ?string
    {
        $module = collect(self::all())->firstWhere('key', $key);
        if ($module && is_array($module) && isset($module['name'])) {
            return $module['name'];
        }
        return null;
    }

    /**
     * Retorna o ícone (opcional).
     */
    public static function getIcon(string $key): ?string
    {
        $module = collect(self::all())->firstWhere('key', $key);
        if ($module && is_array($module) && isset($module['icon'])) {
            return $module['icon'];
        }
        return null;
    }
}
