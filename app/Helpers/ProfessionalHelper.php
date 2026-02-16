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
        try {
            $settings = TenantSetting::getAll();

            // Se personalização está desabilitada, retorna sempre "Médico"
            if (!($settings['professional.customization_enabled'] ?? false)) {
                return 'Médico';
            }

            // 1. Prioridade: Rótulo individual do profissional
            if ($doctor && isset($doctor->label_singular) && !empty(trim($doctor->label_singular))) {
                $label = trim($doctor->label_singular);
                if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                    return $label;
                }
            }

            // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
            if ($doctor) {
                $specialty = $doctor->specialties?->first();
                if ($specialty && isset($specialty->label_singular) && !empty(trim($specialty->label_singular))) {
                    $label = trim($specialty->label_singular);
                    if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                        return $label;
                    }
                }
            }

            // 3. Prioridade: Rótulo global personalizado
            if (isset($settings['professional.label_singular']) && !empty(trim($settings['professional.label_singular']))) {
                $label = trim($settings['professional.label_singular']);
                if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                    return $label;
                }
            }

            // Padrão quando personalização está habilitada mas não há customização
            return 'Profissional';
        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::error('Erro em professional_label_singular: ' . $e->getMessage());
            // Em caso de qualquer erro, retorna o fallback seguro
            return 'Profissional';
        }
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
        try {
            $settings = TenantSetting::getAll();

            // Se personalização está desabilitada, retorna sempre "Médicos"
            if (!($settings['professional.customization_enabled'] ?? false)) {
                return 'Médicos';
            }

            // 1. Prioridade: Rótulo individual do profissional
            if ($doctor && isset($doctor->label_plural) && !empty(trim($doctor->label_plural))) {
                $label = trim($doctor->label_plural);
                if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                    return $label;
                }
            }

            // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
            if ($doctor) {
                $specialty = $doctor->specialties?->first();
                if ($specialty && isset($specialty->label_plural) && !empty(trim($specialty->label_plural))) {
                    $label = trim($specialty->label_plural);
                    if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                        return $label;
                    }
                }
            }

            // 3. Prioridade: Rótulo global personalizado
            if (isset($settings['professional.label_plural']) && !empty(trim($settings['professional.label_plural']))) {
                $label = trim($settings['professional.label_plural']);
                if (strlen($label) > 1 && !in_array($label, ['s', 'ss', ','])) {
                    return $label;
                }
            }

            // Padrão quando personalização está habilitada mas não há customização
            return 'Profissionais';
        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::error('Erro em professional_label_plural: ' . $e->getMessage());
            // Em caso de qualquer erro, retorna o fallback seguro
            return 'Profissionais';
        }
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
        if ($doctor && isset($doctor->registration_label) && !empty(trim($doctor->registration_label))) {
            return trim($doctor->registration_label);
        }

        // 2. Prioridade: Rótulo da especialidade (busca automaticamente do doctor)
        if ($doctor) {
            $specialty = $doctor->specialties?->first();
            if ($specialty && isset($specialty->registration_label) && !empty(trim($specialty->registration_label))) {
                return trim($specialty->registration_label);
            }
        }

        // 3. Prioridade: Rótulo global personalizado
        if (isset($settings['professional.registration_label']) && !empty(trim($settings['professional.registration_label']))) {
            return trim($settings['professional.registration_label']);
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
        if ($doctor && isset($doctor->registration_value) && !empty(trim($doctor->registration_value))) {
            return trim($doctor->registration_value);
        }

        // Se não tiver registration_value personalizado, tenta usar crm_number + crm_state
        if ($doctor && isset($doctor->crm_number) && !empty(trim($doctor->crm_number))) {
            $crm = trim($doctor->crm_number);
            if (isset($doctor->crm_state) && !empty(trim($doctor->crm_state))) {
                $crm .= '/' . trim($doctor->crm_state);
            }
            return $crm;
        }

        return null;
    }
}

if (!function_exists('safe_menu_label')) {
    /**
     * Garante que um label de menu nunca seja vazio ou inválido
     * Aplica regras de fallback para textos dinâmicos do menu
     * 
     * @param string $label O label a ser validado
     * @param string $fallback Label padrão caso o original seja inválido
     * @return string Label seguro para exibição
     */
    function safe_menu_label(string $label, string $fallback = 'Item'): string
    {
        // Verifica se o label é nulo ou não é string
        if ($label === null || !is_string($label)) {
            return $fallback;
        }
        
        // Remove espaços em branco extras
        $cleanLabel = trim($label);
        
        // Verifica se está vazio após limpar
        if (empty($cleanLabel)) {
            return $fallback;
        }
        
        // Verifica se contém apenas caracteres inválidos (ex: apenas "s", "ss" ou vírgulas)
        if (strlen($cleanLabel) <= 2 && !preg_match('/^[a-zA-Zá-úÁ-Ú]{2,}$/', $cleanLabel)) {
            return $fallback;
        }
        
        // Verifica se termina com vírgula (indicando concatenação incompleta)
        if (str_ends_with($cleanLabel, ',')) {
            return $fallback;
        }
        
        // Verifica se contém apenas espaços ou caracteres especiais
        if (!preg_match('/[a-zA-Zá-úÁ-Ú]/', $cleanLabel)) {
            return $fallback;
        }
        
        // Verifica casos específicos problemáticos
        $invalidPatterns = ['^s$', '^ss$', '^,$', '^\s*$'];
        foreach ($invalidPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/', $cleanLabel)) {
                return $fallback;
            }
        }
        
        return $cleanLabel;
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

