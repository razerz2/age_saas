<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Patient;
use App\Support\Tenant\CampaignPatientRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CampaignAudienceBuilder
{
    /**
     * @return array<int, array{
     *     target_type:string,
     *     target_id:?int,
     *     email:?string,
     *     phone:?string,
     *     vars_json:array<string,mixed>
     * }>
     */
    public function build(Campaign $campaign, array $context = []): array
    {
        $audience = is_array($campaign->audience_json) ? $campaign->audience_json : [];
        $source = strtolower(trim((string) ($audience['source'] ?? 'patients')));

        if ($source !== 'patients') {
            return [];
        }

        $trigger = $this->resolveTrigger($campaign, $context);
        $timezone = $this->resolveTimezone($campaign, $context);
        $localNow = $this->resolveLocalNow($timezone, $context);
        $inactivityDays = max(1, (int) data_get($campaign->automation_json, 'inactivity_days', config('campaigns.automation.inactive_days', 60)));

        $query = Patient::query()->from('patients');
        $filterIsActive = $this->normalizeNullableBool(data_get($audience, 'filters.patient.is_active'));
        if ($filterIsActive !== null) {
            $query->where('patients.is_active', $filterIsActive);
        } else {
            $query->where('patients.is_active', true);
        }

        if (strtolower((string) $campaign->type) === 'automated') {
            $query = CampaignPatientRules::applyToPatientQuery($query, $campaign->rules_json, $timezone);
        }

        if ($trigger === 'birthday') {
            $query->whereNotNull('patients.birth_date')
                ->whereMonth('patients.birth_date', $localNow->month)
                ->whereDay('patients.birth_date', $localNow->day);
        }

        if ($trigger === 'inactive_patients') {
            try {
                $query = $this->applyInactivePatientsFilter($query, $localNow, $inactivityDays);
            } catch (Throwable $exception) {
                Log::warning('campaign_audience_inactive_patients_not_available', [
                    'campaign_id' => (int) $campaign->id,
                    'error' => $exception->getMessage(),
                ]);
                return [];
            }
        }

        $requireEmail = $this->normalizeNullableBool(data_get($audience, 'require.email')) === true;
        $requireWhatsapp = $this->normalizeNullableBool(data_get($audience, 'require.whatsapp')) === true;
        $selectColumns = [
            'patients.id',
            'patients.full_name',
            'patients.email',
            'patients.phone',
            'patients.is_active',
            'patients.birth_date',
        ];

        if ($trigger === 'inactive_patients') {
            $selectColumns[] = 'appointments_summary.last_appointment_at';
        } else {
            $selectColumns[] = DB::raw('NULL as last_appointment_at');
        }

        $items = [];
        try {
            $patients = $query->get($selectColumns);
        } catch (Throwable $exception) {
            Log::warning('campaign_audience_build_failed', [
                'campaign_id' => (int) $campaign->id,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        foreach ($patients as $patient) {
            $fullName = trim((string) ($patient->full_name ?? ''));
            $firstName = $this->extractFirstName($fullName);

            $email = $this->normalizeNullableString($patient->email ?? null);
            $phone = $this->normalizeNullableString($patient->phone ?? null);

            if ($requireEmail && $email === null) {
                continue;
            }

            if ($requireWhatsapp && $phone === null) {
                continue;
            }

            $patientIdRaw = (string) ($patient->id ?? '');
            $targetId = ctype_digit($patientIdRaw) ? (int) $patientIdRaw : null;
            $birthDate = $patient->birth_date ? Carbon::parse($patient->birth_date, $timezone) : null;
            $lastAppointmentAt = $patient->last_appointment_at ? Carbon::parse((string) $patient->last_appointment_at, $timezone) : null;
            $resolvedInactivityDays = $lastAppointmentAt
                ? max(0, $lastAppointmentAt->diffInDays($localNow, false))
                : $inactivityDays;

            $varsPatient = [
                'id' => $patientIdRaw !== '' ? $patientIdRaw : null,
                'full_name' => $fullName !== '' ? $fullName : null,
                'first_name' => $firstName,
                'email' => $email,
                'phone' => $phone,
                'is_active' => (bool) $patient->is_active,
            ];

            if ($birthDate) {
                $varsPatient['birthdate_day_month'] = $birthDate->format('d/m');
            }

            $items[] = [
                'target_type' => 'patient',
                'target_id' => $targetId,
                'email' => $email,
                'phone' => $phone,
                'vars_json' => [
                    'patient' => $varsPatient,
                    'now' => [
                        'date' => $localNow->format('Y-m-d'),
                    ],
                    'inactivity_days' => $resolvedInactivityDays,
                ],
            ];
        }

        return $items;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeNullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    private function extractFirstName(string $fullName): ?string
    {
        $normalized = trim($fullName);
        if ($normalized === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $normalized);
        $first = is_array($parts) ? trim((string) ($parts[0] ?? '')) : '';

        return $first !== '' ? $first : null;
    }

    /**
     * @param array<string,mixed> $context
     */
    private function resolveTrigger(Campaign $campaign, array $context): string
    {
        $contextTrigger = strtolower(trim((string) ($context['trigger'] ?? '')));
        if (in_array($contextTrigger, ['birthday', 'inactive_patients'], true)) {
            return $contextTrigger;
        }

        $automationTrigger = strtolower(trim((string) data_get($campaign->automation_json, 'trigger', '')));
        if (in_array($automationTrigger, ['birthday', 'inactive_patients'], true)) {
            return $automationTrigger;
        }

        return '';
    }

    /**
     * @param array<string,mixed> $context
     */
    private function resolveTimezone(Campaign $campaign, array $context): string
    {
        $timezone = trim((string) data_get($context, 'context.automation.timezone', ''));
        if ($timezone === '') {
            $timezone = trim((string) ($campaign->timezone ?? ''));
        }
        if ($timezone === '') {
            $timezone = trim((string) data_get($campaign->automation_json, 'timezone', ''));
        }

        if ($timezone === '') {
            $timezone = (string) config('app.timezone', 'America/Sao_Paulo');
        }

        try {
            Carbon::now($timezone);
            return $timezone;
        } catch (Throwable) {
            return (string) config('app.timezone', 'America/Sao_Paulo');
        }
    }

    /**
     * @param array<string,mixed> $context
     */
    private function resolveLocalNow(string $timezone, array $context): Carbon
    {
        $rawLocalNow = data_get($context, 'context.automation.local_now');
        if ($rawLocalNow instanceof Carbon) {
            return $rawLocalNow->copy()->timezone($timezone);
        }

        if (is_string($rawLocalNow) && trim($rawLocalNow) !== '') {
            try {
                return Carbon::parse($rawLocalNow, $timezone);
            } catch (Throwable) {
                // noop
            }
        }

        return Carbon::now($timezone);
    }

    private function applyInactivePatientsFilter(Builder $query, Carbon $localNow, int $inactivityDays): Builder
    {
        $threshold = $localNow->copy()->subDays($inactivityDays)->startOfDay();
        $lastAppointmentSubquery = Appointment::query()
            ->selectRaw('patient_id, MAX(starts_at) as last_appointment_at')
            ->whereNotIn('status', ['canceled', 'cancelled'])
            ->groupBy('patient_id');

        return $query
            ->leftJoinSub($lastAppointmentSubquery, 'appointments_summary', function ($join) {
                $join->on('appointments_summary.patient_id', '=', 'patients.id');
            })
            ->where(function (Builder $builder) use ($threshold) {
                $builder->whereNull('appointments_summary.last_appointment_at')
                    ->orWhere('appointments_summary.last_appointment_at', '<', $threshold);
            });
    }
}
