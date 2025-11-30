<?php

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\MedicalSpecialty;

/**
 * =====================================================
 *  PROFESSIONAL HELPER - Personalização de Profissionais
 * =====================================================
 * 
 * Este helper gerencia os rótulos de profissionais (Médico, Profissional, etc.)
 * respeitando a configuração de personalização habilitada/desabilitada.
 * 
 * Hierarquia de prioridade (quando personalização está habilitada):
 * 1. Registro individual do profissional
 * 2. Registro da especialidade
 * 3. Registro global personalizado
 * 4. Padrão (Profissional / Profissionais / Registro Profissional)
 * 
 * Quando personalização está DESABILITADA:
 * - Sempre retorna: Médico / Médicos / CRM
 */

if (!function_exists('professional_label_singular')) {
    /**
     * Retorna o rótulo singular do profissional
     * 
     * @param \App\Models\Tenant\Doctor|null $doctor Profissional individual para verificar rótulo personalizado
     * @return string
     */
    function professional_label_singular($doctor = null): string
    {
        $settings = TenantSetting::getAll();

        // Se personalização está desabilitada, retorna sempre "Médico"
        if (!($settings['professional.customization_enabled'] ?? false)) {
            return 'Médico';
        }

        // 1. Prioridade: Rótulo individual do profissional
        if ($doctor && isset($doctor->label_singular) && !empty($doctor->label_singular)) {
            return $doctor->label_singular;
        }

        // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
        if ($doctor) {
            $specialty = $doctor->specialties?->first();
            if ($specialty && isset($specialty->label_singular) && !empty($specialty->label_singular)) {
                return $specialty->label_singular;
            }
        }

        // 3. Prioridade: Rótulo global personalizado
        if (isset($settings['professional.label_singular']) && !empty($settings['professional.label_singular'])) {
            return $settings['professional.label_singular'];
        }

        // Padrão quando personalização está habilitada mas não há customização
        return 'Profissional';
    }
}

if (!function_exists('professional_label_plural')) {
    /**
     * Retorna o rótulo plural do profissional
     * 
     * @param \App\Models\Tenant\Doctor|null $doctor Profissional individual para verificar rótulo personalizado
     * @return string
     */
    function professional_label_plural($doctor = null): string
    {
        $settings = TenantSetting::getAll();

        // Se personalização está desabilitada, retorna sempre "Médicos"
        if (!($settings['professional.customization_enabled'] ?? false)) {
            return 'Médicos';
        }

        // 1. Prioridade: Rótulo individual do profissional
        if ($doctor && isset($doctor->label_plural) && !empty($doctor->label_plural)) {
            return $doctor->label_plural;
        }

        // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
        if ($doctor) {
            $specialty = $doctor->specialties?->first();
            if ($specialty && isset($specialty->label_plural) && !empty($specialty->label_plural)) {
                return $specialty->label_plural;
            }
        }

        // 3. Prioridade: Rótulo global personalizado
        if (isset($settings['professional.label_plural']) && !empty($settings['professional.label_plural'])) {
            return $settings['professional.label_plural'];
        }

        // Padrão quando personalização está habilitada mas não há customização
        return 'Profissionais';
    }
}

if (!function_exists('professional_registration_label')) {
    /**
     * Retorna o rótulo do registro profissional (CRM, CRP, etc.)
     * 
     * @param \App\Models\Tenant\Doctor|null $doctor Profissional individual para verificar rótulo personalizado
     * @return string
     */
    function professional_registration_label($doctor = null): string
    {
        $settings = TenantSetting::getAll();

        // Se personalização está desabilitada, retorna sempre "CRM"
        if (!($settings['professional.customization_enabled'] ?? false)) {
            return 'CRM';
        }

        // 1. Prioridade: Rótulo individual do profissional
        if ($doctor && isset($doctor->registration_label) && !empty($doctor->registration_label)) {
            return $doctor->registration_label;
        }

        // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
        if ($doctor) {
            $specialty = $doctor->specialties?->first();
            if ($specialty && isset($specialty->registration_label) && !empty($specialty->registration_label)) {
                return $specialty->registration_label;
            }
        }

        // 3. Prioridade: Rótulo global personalizado
        if (isset($settings['professional.registration_label']) && !empty($settings['professional.registration_label'])) {
            return $settings['professional.registration_label'];
        }

        // Padrão quando personalização está habilitada mas não há customização
        return 'Registro Profissional';
    }
}

if (!function_exists('professional_registration_value')) {
    /**
     * Retorna o valor do registro profissional individual (ex: CRP 05/12345)
     * 
     * @param \App\Models\Tenant\Doctor|null $doctor Profissional individual
     * @return string|null
     */
    function professional_registration_value($doctor = null): ?string
    {
        // 1. Prioridade: Valor individual do profissional
        if ($doctor && isset($doctor->registration_value) && !empty($doctor->registration_value)) {
            return $doctor->registration_value;
        }

        // Se não tiver registration_value personalizado, tenta usar crm_number + crm_state
        if ($doctor && isset($doctor->crm_number) && !empty($doctor->crm_number)) {
            $crm = $doctor->crm_number;
            if (isset($doctor->crm_state) && !empty($doctor->crm_state)) {
                $crm .= '/' . $doctor->crm_state;
            }
            return $crm;
        }

        return null;
    }
}

if (!function_exists('tenant_setting')) {
    /**
     * Helper rápido para acessar configurações do tenant
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function tenant_setting(string $key, $default = null)
    {
        return TenantSetting::get($key, $default);
    }
}

