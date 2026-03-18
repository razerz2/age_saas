<?php

namespace App\Models\Platform;

class Module
{
    /**
     * Retorna a lista de modulos disponiveis no sistema.
     * Cada modulo tem uma chave (key), nome e icone opcional.
     */
    public static function all(): array
    {
        return [
            ['key' => 'tenants', 'name' => 'Tenants', 'icon' => 'fa-building'],
            ['key' => 'clinic_networks', 'name' => 'Redes de Clinicas', 'icon' => 'fa-hospital'],
            ['key' => 'pre_tenants', 'name' => 'Pre-Cadastros', 'icon' => 'fa-user-plus'],
            ['key' => 'plans', 'name' => 'Planos', 'icon' => 'fa-box'],
            ['key' => 'subscriptions', 'name' => 'Assinaturas', 'icon' => 'fa-receipt'],
            ['key' => 'invoices', 'name' => 'Faturas', 'icon' => 'fa-file-invoice-dollar'],
            ['key' => 'medical_specialties_catalog', 'name' => 'Catalogo Medico', 'icon' => 'fa-stethoscope'],
            ['key' => 'notifications_outbox', 'name' => 'Notificacoes', 'icon' => 'fa-bell'],
            ['key' => 'system_notifications', 'name' => 'Notificacoes do Sistema', 'icon' => 'fa-bell-slash'],
            ['key' => 'platform_email_templates', 'name' => 'Templates de Email Platform', 'icon' => 'fa-envelope'],
            ['key' => 'tenant_email_templates', 'name' => 'Templates de Email Tenant', 'icon' => 'fa-envelope-open-text'],
            ['key' => 'notification_templates', 'name' => 'Layouts de Email', 'icon' => 'fa-palette'],
            ['key' => 'whatsapp_official_templates', 'name' => 'Templates WhatsApp Oficial', 'icon' => 'fa-comment-dots'],
            ['key' => 'whatsapp_official_tenant_templates', 'name' => 'Templates WhatsApp Oficial Tenant', 'icon' => 'fa-building'],
            ['key' => 'whatsapp_unofficial_templates', 'name' => 'Templates Internos WhatsApp Nao Oficial', 'icon' => 'fa-comment-medical'],
            ['key' => 'tenant_default_notification_templates', 'name' => 'Templates Padrao Tenant (Nao Oficial)', 'icon' => 'fa-layer-group'],
            ['key' => 'locations', 'name' => 'Localizacao', 'icon' => 'fa-globe'],
            ['key' => 'users', 'name' => 'Usuarios', 'icon' => 'fa-users'],
            ['key' => 'settings', 'name' => 'Configuracoes', 'icon' => 'fa-cog'],
            ['key' => 'api_tokens', 'name' => 'Tokens de API', 'icon' => 'fa-key'],
            ['key' => 'zapi', 'name' => 'Z-API', 'icon' => 'fa-whatsapp'],
        ];
    }

    /**
     * Retorna o nome amigavel de um modulo a partir da key.
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
     * Retorna o icone (opcional).
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
