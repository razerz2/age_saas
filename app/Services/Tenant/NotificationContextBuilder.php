<?php

namespace App\Services\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Throwable;

class NotificationContextBuilder
{
    /**
     * Example:
     * $tpl = 'Ola {{patient.name}}, confirme em {{links.appointment_confirm}}';
     * $ctx = app(NotificationContextBuilder::class)->buildForAppointment($appointment);
     * $out = app(TemplateRenderer::class)->render($tpl, $ctx);
     */
    public function buildForAppointment(Appointment $appointment): array
    {
        $appointment->loadMissing([
            'patient',
            'doctor.user',
            'doctor.specialties',
            'calendar.doctor.user',
            'specialty',
            'type',
        ]);

        $tenant = $this->resolveCurrentTenant();
        $slug = $this->resolveTenantSlug($tenant);
        $timezone = $this->resolveTimezone();

        $doctor = $appointment->doctor ?? $appointment->calendar?->doctor;
        $doctorName = $this->normalizeString($doctor?->user?->name_full ?? $doctor?->user?->name);
        $doctorSpecialty = $this->normalizeString(
            $appointment->specialty?->name ?? $doctor?->specialties?->first()?->name
        );

        $startsAt = $this->toCarbon($appointment->starts_at, $timezone);
        $endsAt = $this->toCarbon($appointment->ends_at, $timezone);
        $confirmationExpiresAt = $this->toCarbon($appointment->confirmation_expires_at, $timezone);

        $confirmToken = $this->normalizeString($appointment->confirmation_token);

        return [
            'clinic' => $this->buildClinicContext($tenant, $slug),
            'patient' => [
                'name' => $this->normalizeString($appointment->patient?->full_name),
                'phone' => $this->normalizeString($appointment->patient?->phone),
                'email' => $this->normalizeString($appointment->patient?->email),
            ],
            'doctor' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
            ],
            // Alias kept for compatibility with existing placeholders in defaults.
            'professional' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
            ],
            'appointment' => [
                'date' => $this->formatDate($startsAt),
                'time' => $this->formatTime($startsAt),
                'datetime' => $this->formatDateTime($startsAt),
                'starts_at' => $this->formatDateTime($startsAt),
                'ends_at' => $this->formatDateTime($endsAt),
                'type' => $this->normalizeString($appointment->type?->name),
                'mode' => $this->normalizeString($appointment->appointment_mode),
                'status' => $this->normalizeString($appointment->status),
                'confirmation_expires_at' => $this->formatDateTime($confirmationExpiresAt),
            ],
            'links' => [
                'appointment_confirm' => $confirmToken
                    ? $this->buildPublicRoute('public.appointment.confirm', $slug, ['token' => $confirmToken])
                    : null,
                'appointment_cancel' => $confirmToken
                    ? $this->buildPublicRoute('public.appointment.cancel', $slug, ['token' => $confirmToken])
                    : null,
                'appointment_details' => $this->buildPublicRoute('public.appointment.show', $slug, [
                    'appointment_id' => $appointment->id,
                ]),
                'waitlist_offer' => null,
            ],
            'waitlist' => [
                'offer_expires_at' => null,
                'status' => null,
            ],
        ];
    }

    public function buildForWaitlistOffer(AppointmentWaitlistEntry $entry): array
    {
        $entry->loadMissing([
            'patient',
            'doctor.user',
            'doctor.specialties',
        ]);

        $tenant = $this->resolveCurrentTenant();
        $slug = $this->resolveTenantSlug($tenant);
        $timezone = $this->resolveTimezone();

        $doctorName = $this->normalizeString($entry->doctor?->user?->name_full ?? $entry->doctor?->user?->name);
        $doctorSpecialty = $this->normalizeString($entry->doctor?->specialties?->first()?->name);

        $startsAt = $this->toCarbon($entry->starts_at, $timezone);
        $endsAt = $this->toCarbon($entry->ends_at, $timezone);
        $offerExpiresAt = $this->toCarbon($entry->offer_expires_at, $timezone);

        $offerToken = $this->normalizeString($entry->offer_token);

        return [
            'clinic' => $this->buildClinicContext($tenant, $slug),
            'patient' => [
                'name' => $this->normalizeString($entry->patient?->full_name),
                'phone' => $this->normalizeString($entry->patient?->phone),
                'email' => $this->normalizeString($entry->patient?->email),
            ],
            'doctor' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
            ],
            // Alias kept for compatibility with existing placeholders in defaults.
            'professional' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
            ],
            'appointment' => [
                'date' => $this->formatDate($startsAt),
                'time' => $this->formatTime($startsAt),
                'datetime' => $this->formatDateTime($startsAt),
                'starts_at' => $this->formatDateTime($startsAt),
                'ends_at' => $this->formatDateTime($endsAt),
                'type' => null,
                'mode' => null,
                'status' => null,
                'confirmation_expires_at' => null,
            ],
            'links' => [
                'appointment_confirm' => null,
                'appointment_cancel' => null,
                'appointment_details' => null,
                'waitlist_offer' => $offerToken
                    ? $this->buildPublicRoute('public.waitlist.offer.show', $slug, ['token' => $offerToken])
                    : null,
            ],
            'waitlist' => [
                'offer_expires_at' => $this->formatDateTime($offerExpiresAt),
                'status' => $this->normalizeString($entry->status),
            ],
        ];
    }

    private function buildClinicContext(?PlatformTenant $tenant, ?string $slug): array
    {
        return [
            'name' => $this->normalizeString($tenant?->trade_name ?? $tenant?->legal_name),
            'phone' => $this->normalizeString($tenant?->phone),
            'email' => $this->normalizeString($tenant?->email),
            'address' => $this->formatClinicAddress($tenant),
            'slug' => $slug,
        ];
    }

    private function resolveCurrentTenant(): ?PlatformTenant
    {
        $current = tenant();
        if ($current instanceof PlatformTenant) {
            $current->loadMissing(['localizacao.cidade', 'localizacao.estado']);
            return $current;
        }

        return null;
    }

    private function resolveTenantSlug(?PlatformTenant $tenant): ?string
    {
        $slug = $this->normalizeString($tenant?->subdomain);
        if ($slug) {
            return $slug;
        }

        $slugFromRoute = $this->normalizeString(request()->route('slug'));
        if ($slugFromRoute) {
            return $slugFromRoute;
        }

        return $this->normalizeString(session('tenant_slug'));
    }

    private function buildPublicRoute(string $routeName, ?string $slug, array $parameters = []): ?string
    {
        if (!$slug || !Route::has($routeName)) {
            return null;
        }

        try {
            return route($routeName, array_merge(['slug' => $slug], $parameters));
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveTimezone(): string
    {
        $timezone = TenantSetting::get('timezone', config('app.timezone', 'America/Sao_Paulo'));
        $timezone = is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone', 'America/Sao_Paulo');

        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (Throwable) {
            return (string) config('app.timezone', 'America/Sao_Paulo');
        }
    }

    private function toCarbon(mixed $value, string $timezone): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof Carbon) {
                return $value->copy()->timezone($timezone);
            }

            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->timezone($timezone);
            }

            return Carbon::parse((string) $value, $timezone)->timezone($timezone);
        } catch (Throwable) {
            return null;
        }
    }

    private function formatDate(?Carbon $value): ?string
    {
        return $value?->format('d/m/Y');
    }

    private function formatTime(?Carbon $value): ?string
    {
        return $value?->format('H:i');
    }

    private function formatDateTime(?Carbon $value): ?string
    {
        return $value?->format('d/m/Y H:i');
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function formatClinicAddress(?PlatformTenant $tenant): ?string
    {
        $location = $tenant?->localizacao;
        if (!$location) {
            return null;
        }

        $street = $this->normalizeString($location->endereco);
        $number = $this->normalizeString($location->n_endereco);
        $complement = $this->normalizeString($location->complemento);
        $district = $this->normalizeString($location->bairro);
        $city = $this->normalizeString($location->cidade?->nome_cidade);
        $state = $this->normalizeString($location->estado?->uf);
        $zip = $this->normalizeString($location->cep);

        $streetLine = implode(', ', array_values(array_filter([$street, $number], static fn ($item) => $item !== null)));
        if ($streetLine !== '' && $complement !== null) {
            $streetLine .= ' - ' . $complement;
        }

        $cityLine = null;
        if ($city !== null && $state !== null) {
            $cityLine = $city . '/' . $state;
        } elseif ($city !== null) {
            $cityLine = $city;
        } elseif ($state !== null) {
            $cityLine = $state;
        }

        $parts = array_values(array_filter([$streetLine !== '' ? $streetLine : null, $district, $cityLine, $zip], static fn ($item) => $item !== null));
        if ($parts === []) {
            return null;
        }

        return implode(' - ', $parts);
    }
}

