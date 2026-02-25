<?php

namespace App\Models\Tenant;

use App\Services\FeatureAccessService;
use App\Models\Platform\Tenant;

class Module
{
    /**
     * Mapeamento de módulos para features do plano
     * Módulos que não estão no mapeamento são sempre disponíveis (módulos padrão)
     */
    protected static function getModuleFeatureMap(): array
    {
        return [
            'finance' => 'finance',
            'online_appointments' => 'online_appointments',
            'medical_appointments' => 'medical_appointments',
            'integrations' => 'integrations',
            'users' => 'users',
            'business_hours' => 'business_hours',
            'forms' => 'forms',
            'reports' => 'reports',
            'settings' => 'settings',
        ];
    }

    /**
     * Retorna a lista de módulos disponíveis no sistema para o Tenant.
     * Cada módulo tem uma chave (key), nome e ícone opcional.
     */
    public static function all(): array
    {
        return [
            ['key' => 'appointments', 'name' => 'Atendimentos', 'icon' => 'fa-calendar-check'],
            ['key' => 'online_appointments', 'name' => 'Consultas Online', 'icon' => 'fa-video'],
            ['key' => 'medical_appointments', 'name' => 'Atendimento Médico', 'icon' => 'fa-user-md'],
            ['key' => 'patients', 'name' => 'Pacientes', 'icon' => 'fa-users'],
            ['key' => 'doctors', 'name' => 'Médicos', 'icon' => 'fa-stethoscope'],
            ['key' => 'calendar', 'name' => 'Agenda', 'icon' => 'fa-calendar'],
            ['key' => 'specialties', 'name' => 'Especialidades', 'icon' => 'fa-pulse'],
            ['key' => 'users', 'name' => 'Usuários', 'icon' => 'fa-user-circle'],
            ['key' => 'business_hours', 'name' => 'Horários Médicos', 'icon' => 'fa-clock'],
            ['key' => 'forms', 'name' => 'Formulários', 'icon' => 'fa-file-alt'],
            ['key' => 'reports', 'name' => 'Relatórios', 'icon' => 'fa-chart-bar'],
            ['key' => 'integrations', 'name' => 'Integrações', 'icon' => 'fa-plug'],
            ['key' => 'campaigns', 'name' => 'Campanhas', 'icon' => 'fa-bullhorn'],
            ['key' => 'finance', 'name' => 'Financeiro', 'icon' => 'fa-dollar-sign'],
            ['key' => 'settings', 'name' => 'Configurações', 'icon' => 'fa-cog'],
        ];
    }

    /**
     * Lista de módulos padrão que sempre estão disponíveis
     */
    protected static function getDefaultModules(): array
    {
        return ['appointments', 'patients', 'doctors', 'calendar', 'specialties'];
    }

    /**
     * Retorna a chave de configuração para verificar se o módulo está habilitado
     * 
     * @param string $moduleKey
     * @return string
     */
    protected static function getModuleEnabledKey(string $moduleKey): string
    {
        // Mapeamento de módulos para suas chaves de configuração
        $keyMap = [
            'finance' => 'finance.enabled',
            // Adicione outros módulos aqui conforme necessário
        ];

        // Se existe mapeamento específico, usa ele
        if (isset($keyMap[$moduleKey])) {
            return $keyMap[$moduleKey];
        }

        // Padrão: modules.{moduleKey}.enabled
        return "modules.{$moduleKey}.enabled";
    }

    /**
     * Retorna apenas os módulos disponíveis baseado no plano e configurações da tenant
     * 
     * @return array
     */
    public static function available(): array
    {
        $allModules = self::all();
        $featureMap = self::getModuleFeatureMap();
        $defaultModules = self::getDefaultModules();
        $featureService = app(FeatureAccessService::class);
        $tenant = Tenant::current();

        return collect($allModules)->filter(function ($module) use ($featureMap, $defaultModules, $featureService, $tenant) {
            $moduleKey = $module['key'];

            // Módulos padrão sempre estão disponíveis
            if (in_array($moduleKey, $defaultModules)) {
                return true;
            }

            // Verifica se o módulo requer uma feature do plano
            $requiresFeature = isset($featureMap[$moduleKey]);
            
            if ($requiresFeature) {
                $featureName = $featureMap[$moduleKey];
                
                // Verifica se a feature está no plano
                if (!$featureService->hasFeature($featureName, $tenant)) {
                    return false;
                }
            }

            // Verifica se o módulo está habilitado na tenant
            // A chave de configuração varia por módulo
            $moduleEnabledKey = self::getModuleEnabledKey($moduleKey);
            $enabledValue = TenantSetting::get($moduleEnabledKey);
            
            // Para o módulo financeiro, comportamento especial:
            // Se não existe configuração, considera DESABILITADO (padrão é false)
            if ($moduleKey === 'finance') {
                if ($enabledValue === null) {
                    return false; // Financeiro desabilitado por padrão
                }
                // Verifica se está explicitamente habilitado
                return $enabledValue === 'true' || $enabledValue === true || $enabledValue === '1' || $enabledValue === 1;
            }
            
            // Para outros módulos
            // Se não existe configuração
            if ($enabledValue === null) {
                // Se o módulo requer feature do plano e a feature está no plano, aparece por padrão
                // Se não requer feature, também aparece por padrão
                return true;
            }
            
            // Se existe configuração, verifica se está habilitado
            // Se o valor for 'false', '0', 0 ou false, não aparece
            if ($enabledValue === 'false' || $enabledValue === false || $enabledValue === '0' || $enabledValue === 0) {
                return false;
            }
            
            // Caso contrário, está habilitado
            return true;
        })->values()->all();
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
     * Retorna o ícone (opcional) de um módulo.
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
