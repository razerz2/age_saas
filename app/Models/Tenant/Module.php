<?php

namespace App\Models\Tenant;

class Module
{
    /**
     * Retorna a lista de módulos disponíveis no sistema para o Tenant.
     * Cada módulo tem uma chave (key), nome e ícone opcional.
     */
    public static function all(): array
    {
        return [
            ['key' => 'appointments', 'name' => 'Atendimentos', 'icon' => 'fa-calendar-check'],
            ['key' => 'patients', 'name' => 'Pacientes', 'icon' => 'fa-users'],
            ['key' => 'doctors', 'name' => 'Médicos', 'icon' => 'fa-stethoscope'],
            ['key' => 'calendar', 'name' => 'Agenda', 'icon' => 'fa-calendar'],
            ['key' => 'specialties', 'name' => 'Especialidades', 'icon' => 'fa-pulse'],
            ['key' => 'users', 'name' => 'Usuários', 'icon' => 'fa-user-circle'],
            ['key' => 'business_hours', 'name' => 'Horários Médicos', 'icon' => 'fa-clock'],
            ['key' => 'forms', 'name' => 'Formulários', 'icon' => 'fa-file-alt'],
            ['key' => 'integrations', 'name' => 'Integrações', 'icon' => 'fa-plug'],
        ];
    }

    /**
     * Retorna o nome amigável de um módulo a partir da key.
     */
    public static function getName(string $key): ?string
    {
        $module = collect(self::all())->firstWhere('key', $key);
        return $module['name'] ?? null;
    }

    /**
     * Retorna o ícone (opcional) de um módulo.
     */
    public static function getIcon(string $key): ?string
    {
        $module = collect(self::all())->firstWhere('key', $key);
        return $module['icon'] ?? null;
    }
}
