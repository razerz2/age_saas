<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\TenantSetting;
use Illuminate\Database\Eloquent\Model;

class ProfessionalLabelService
{
    public const PROFILE_MEDICAL = 'medical';
    public const PROFILE_PSYCHOLOGY = 'psychology';
    public const PROFILE_DENTISTRY = 'dentistry';
    public const PROFILE_PHYSIOTHERAPY = 'physiotherapy';
    public const PROFILE_MULTIDISCIPLINARY = 'multidisciplinary';
    public const PROFILE_CUSTOM = 'custom';

    private const DEFAULT_SINGULAR = 'Médico';
    private const DEFAULT_PLURAL = 'Médicos';
    private const DEFAULT_REGISTRATION = 'CRM';

    /**
     * @return array<string, string>
     */
    public static function environmentProfileOptions(): array
    {
        return [
            self::PROFILE_MEDICAL => 'Clínica Médica',
            self::PROFILE_PSYCHOLOGY => 'Psicologia',
            self::PROFILE_DENTISTRY => 'Odontologia',
            self::PROFILE_PHYSIOTHERAPY => 'Fisioterapia',
            self::PROFILE_MULTIDISCIPLINARY => 'Multidisciplinar',
            self::PROFILE_CUSTOM => 'Personalizado',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedEnvironmentProfiles(): array
    {
        return array_keys(self::environmentProfileOptions());
    }

    /**
     * @return array<string, array{singular:string,plural:string,registration:string}>
     */
    public static function presets(): array
    {
        return [
            self::PROFILE_MEDICAL => [
                'singular' => 'Médico',
                'plural' => 'Médicos',
                'registration' => 'CRM',
            ],
            self::PROFILE_PSYCHOLOGY => [
                'singular' => 'Psicólogo',
                'plural' => 'Psicólogos',
                'registration' => 'CRP',
            ],
            self::PROFILE_DENTISTRY => [
                'singular' => 'Dentista',
                'plural' => 'Dentistas',
                'registration' => 'CRO',
            ],
            self::PROFILE_PHYSIOTHERAPY => [
                'singular' => 'Fisioterapeuta',
                'plural' => 'Fisioterapeutas',
                'registration' => 'CREFITO',
            ],
            self::PROFILE_MULTIDISCIPLINARY => [
                'singular' => 'Profissional',
                'plural' => 'Profissionais',
                'registration' => 'Conselho',
            ],
            self::PROFILE_CUSTOM => [
                'singular' => '',
                'plural' => '',
                'registration' => '',
            ],
        ];
    }

    public function singular(mixed $doctor = null, mixed $specialty = null): string
    {
        return $this->resolveContextLabels($doctor, $specialty)['singular'];
    }

    public function plural(mixed $doctor = null, mixed $specialty = null): string
    {
        return $this->resolveContextLabels($doctor, $specialty)['plural'];
    }

    public function registration(mixed $doctor = null, mixed $specialty = null): string
    {
        return $this->resolveContextLabels($doctor, $specialty)['registration'];
    }

    /**
     * @return array{singular:string,plural:string,registration:string}
     */
    public function labels(mixed $doctor = null, mixed $specialty = null): array
    {
        return $this->resolveContextLabels($doctor, $specialty);
    }

    public function environmentProfile(): string
    {
        if (!$this->customizationEnabled()) {
            return self::PROFILE_MEDICAL;
        }

        $value = TenantSetting::get('professional.environment_profile', self::PROFILE_MEDICAL);

        return $this->sanitizeEnvironmentProfile($value, self::PROFILE_MEDICAL);
    }

    public function sanitizeEnvironmentProfile(mixed $profile, string $fallback = self::PROFILE_MEDICAL): string
    {
        $value = strtolower(trim((string) $profile));

        if (in_array($value, self::allowedEnvironmentProfiles(), true)) {
            return $value;
        }

        return $fallback;
    }

    /**
     * @return array{singular:string,plural:string,registration:string}
     */
    private function resolveContextLabels(mixed $doctor = null, mixed $specialty = null): array
    {
        $defaults = $this->defaults();

        if (!$this->customizationEnabled()) {
            return $defaults;
        }

        $resolved = $this->tenantLabels();
        $doctorModel = $this->resolveDoctorModel($doctor);
        $specialtyModel = $this->resolveSpecialtyModel($specialty);

        if (!$specialtyModel && $doctorModel) {
            $specialtyModel = $this->resolvePrimarySpecialtyFromDoctor($doctorModel);
        }

        if ($specialtyModel) {
            $resolved = $this->applyOverrides($resolved, [
                'singular' => $specialtyModel->label_singular,
                'plural' => $specialtyModel->label_plural,
                'registration' => $specialtyModel->registration_label,
            ]);
        }

        if ($doctorModel) {
            $resolved = $this->applyOverrides($resolved, [
                'singular' => $doctorModel->label_singular,
                'plural' => $doctorModel->label_plural,
                'registration' => $doctorModel->registration_label,
            ]);
        }

        foreach (array_keys($defaults) as $key) {
            if ($resolved[$key] === '') {
                $resolved[$key] = $defaults[$key];
            }
        }

        return $resolved;
    }

    /**
     * @return array{singular:string,plural:string,registration:string}
     */
    private function defaults(): array
    {
        return [
            'singular' => self::DEFAULT_SINGULAR,
            'plural' => self::DEFAULT_PLURAL,
            'registration' => self::DEFAULT_REGISTRATION,
        ];
    }

    /**
     * @return array{singular:string,plural:string,registration:string}
     */
    private function tenantLabels(): array
    {
        return [
            'singular' => $this->normalizeLabel(TenantSetting::get('professional.label_singular', '')),
            'plural' => $this->normalizeLabel(TenantSetting::get('professional.label_plural', '')),
            'registration' => $this->normalizeLabel(TenantSetting::get('professional.registration_label', '')),
        ];
    }

    /**
     * @param array{singular:string,plural:string,registration:string} $base
     * @param array{singular:mixed,plural:mixed,registration:mixed} $overrides
     * @return array{singular:string,plural:string,registration:string}
     */
    private function applyOverrides(array $base, array $overrides): array
    {
        foreach (['singular', 'plural', 'registration'] as $key) {
            $normalized = $this->normalizeLabel($overrides[$key] ?? null);
            if ($normalized !== '') {
                $base[$key] = $normalized;
            }
        }

        return $base;
    }

    private function normalizeLabel(mixed $value): string
    {
        return trim((string) $value);
    }

    private function resolveDoctorModel(mixed $doctor): ?Doctor
    {
        if ($doctor instanceof Doctor) {
            $doctor->loadMissing('primarySpecialty');
            return $doctor;
        }

        if ($doctor instanceof Model && $doctor->getTable() === 'doctors') {
            return Doctor::query()
                ->with('primarySpecialty')
                ->find($doctor->getKey());
        }

        if (is_string($doctor) && trim($doctor) !== '') {
            return Doctor::query()
                ->with('primarySpecialty')
                ->find(trim($doctor));
        }

        if (is_array($doctor) && isset($doctor['id']) && is_string($doctor['id']) && trim($doctor['id']) !== '') {
            return Doctor::query()
                ->with('primarySpecialty')
                ->find(trim($doctor['id']));
        }

        return null;
    }

    private function resolveSpecialtyModel(mixed $specialty): ?MedicalSpecialty
    {
        if ($specialty instanceof MedicalSpecialty) {
            return $specialty;
        }

        if ($specialty instanceof Model && $specialty->getTable() === 'medical_specialties') {
            return MedicalSpecialty::query()->find($specialty->getKey());
        }

        if (is_string($specialty) && trim($specialty) !== '') {
            return MedicalSpecialty::query()->find(trim($specialty));
        }

        if (is_array($specialty) && isset($specialty['id']) && is_string($specialty['id']) && trim($specialty['id']) !== '') {
            return MedicalSpecialty::query()->find(trim($specialty['id']));
        }

        return null;
    }

    private function resolvePrimarySpecialtyFromDoctor(Doctor $doctor): ?MedicalSpecialty
    {
        if (!$doctor->primary_specialty_id) {
            return null;
        }

        if ($doctor->relationLoaded('primarySpecialty')) {
            return $doctor->primarySpecialty;
        }

        return $doctor->primarySpecialty()->first();
    }

    private function customizationEnabled(): bool
    {
        $value = TenantSetting::get('professional.customization_enabled', 'false');

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}
