<?php

use App\Models\Tenant\TenantSetting;
use App\Services\Tenant\ProfessionalLabelService;

if (!function_exists('professional_label_singular')) {
    function professional_label_singular($doctor = null, $specialty = null): string
    {
        try {
            /** @var ProfessionalLabelService $service */
            $service = app(ProfessionalLabelService::class);
            return $service->singular($doctor, $specialty);
        } catch (\Throwable) {
            return 'Médico';
        }
    }
}

if (!function_exists('professional_label_plural')) {
    function professional_label_plural($doctor = null, $specialty = null): string
    {
        try {
            /** @var ProfessionalLabelService $service */
            $service = app(ProfessionalLabelService::class);
            return $service->plural($doctor, $specialty);
        } catch (\Throwable) {
            return 'Médicos';
        }
    }
}

if (!function_exists('professional_registration_label')) {
    function professional_registration_label($doctor = null, $specialty = null): string
    {
        try {
            /** @var ProfessionalLabelService $service */
            $service = app(ProfessionalLabelService::class);
            return $service->registration($doctor, $specialty);
        } catch (\Throwable) {
            return 'CRM';
        }
    }
}

if (!function_exists('professional_registration_value')) {
    function professional_registration_value($doctor = null): ?string
    {
        if ($doctor && isset($doctor->registration_value) && !empty(trim((string) $doctor->registration_value))) {
            return trim((string) $doctor->registration_value);
        }

        if ($doctor && isset($doctor->crm_number) && !empty(trim((string) $doctor->crm_number))) {
            $value = trim((string) $doctor->crm_number);
            if (isset($doctor->crm_state) && !empty(trim((string) $doctor->crm_state))) {
                $value .= '/' . trim((string) $doctor->crm_state);
            }

            return $value;
        }

        return null;
    }
}

if (!function_exists('safe_menu_label')) {
    function safe_menu_label(string $label, string $fallback = 'Item'): string
    {
        $clean = trim((string) $label);
        if ($clean === '') {
            return $fallback;
        }

        if (!preg_match('/[a-zA-Zá-úÁ-Ú]/u', $clean)) {
            return $fallback;
        }

        if (str_ends_with($clean, ',')) {
            return $fallback;
        }

        if (preg_match('/^(s|ss|,)\s*$/iu', $clean) === 1) {
            return $fallback;
        }

        return $clean;
    }
}

if (!function_exists('tenant_setting')) {
    function tenant_setting(string $key, $default = null)
    {
        return TenantSetting::get($key, $default);
    }
}

if (!function_exists('tenant_setting_bool')) {
    function tenant_setting_bool(string $key, bool $default = false): bool
    {
        $defaultValue = $default ? 'true' : 'false';
        $value = tenant_setting($key, $defaultValue);

        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('tenant_setting_int')) {
    function tenant_setting_int(string $key, int $default = 0): int
    {
        $value = tenant_setting($key, $default);

        if ($value === null || $value === '') {
            return $default;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT);

        return $intValue === false ? $default : (int) $intValue;
    }
}

if (!function_exists('tenant_setting_nullable_int')) {
    function tenant_setting_nullable_int(string $key, ?int $default = null): ?int
    {
        $value = tenant_setting($key, $default);

        if ($value === null || $value === '') {
            return $default;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT);

        return $intValue === false ? $default : (int) $intValue;
    }
}
