<?php

namespace App\Services\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormResponse;
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
            'onlineInstructions',
        ]);

        $tenant = $this->resolveCurrentTenant();
        $slug = $this->resolveTenantSlug($tenant);
        $timezone = $this->resolveTimezone();

        $doctor = $appointment->doctor ?? $appointment->calendar?->doctor;
        $doctorName = $this->normalizeString($doctor?->user?->name_full ?? $doctor?->user?->name);
        $doctorPhone = $this->normalizeString($doctor?->user?->telefone ?? $doctor?->user?->phone);
        $doctorEmail = $this->normalizeString($doctor?->user?->email);
        $doctorSpecialty = $this->normalizeString(
            $appointment->specialty?->name ?? $doctor?->specialties?->first()?->name
        );

        $startsAt = $this->toCarbon($appointment->starts_at, $timezone);
        $endsAt = $this->toCarbon($appointment->ends_at, $timezone);
        $confirmationExpiresAt = $this->toCarbon($appointment->confirmation_expires_at, $timezone);

        $confirmToken = $this->normalizeString($appointment->confirmation_token);
        $appointmentDetailsParams = [
            'appointment_id' => $appointment->id,
        ];
        if ($confirmToken) {
            $appointmentDetailsParams['token'] = $confirmToken;
        }
        $onlineInstructions = $appointment->onlineInstructions;
        $form = Form::getFormForAppointment($appointment);
        $sentByEmailAt = $this->toCarbon($onlineInstructions?->sent_by_email_at, $timezone);
        $sentByWhatsappAt = $this->toCarbon($onlineInstructions?->sent_by_whatsapp_at, $timezone);
        $isOnlineAppointment = $this->normalizeString($appointment->appointment_mode) === 'online';

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
                'phone' => $doctorPhone,
                'email' => $doctorEmail,
            ],
            // Alias kept for compatibility with existing placeholders in defaults.
            'professional' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
                'phone' => $doctorPhone,
                'email' => $doctorEmail,
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
            'online' => [
                'is_online' => $isOnlineAppointment,
                'meeting_link' => $this->normalizeString($onlineInstructions?->meeting_link),
                'meeting_app' => $this->normalizeString($onlineInstructions?->meeting_app),
                'general_instructions' => $this->normalizeString($onlineInstructions?->general_instructions),
                'patient_instructions' => $this->normalizeString($onlineInstructions?->patient_instructions),
                'instructions_sent' => $sentByEmailAt !== null || $sentByWhatsappAt !== null,
                'instructions_sent_email_at' => $this->formatDateTime($sentByEmailAt),
                'instructions_sent_whatsapp_at' => $this->formatDateTime($sentByWhatsappAt),
            ],
            'form' => [
                'id' => $form?->id ? (string) $form->id : null,
                'name' => $this->normalizeString($form?->name),
            ],
            'links' => [
                'appointment_confirm' => $confirmToken
                    ? $this->buildPublicRoute('public.appointment.confirm', $slug, ['token' => $confirmToken])
                    : null,
                'appointment_cancel' => $confirmToken
                    ? $this->buildPublicRoute('public.appointment.cancel', $slug, ['token' => $confirmToken])
                    : null,
                'appointment_details' => $this->buildPublicRoute('public.appointment.show', $slug, $appointmentDetailsParams),
                'online_appointment_details' => $isOnlineAppointment
                    ? $this->buildTenantRoute('tenant.online-appointments.show', $slug, ['appointment' => $appointment->id])
                    : null,
                'form_fill' => $form?->id
                    ? $this->buildPublicRoute('public.form.response.create', $slug, [
                        'form' => (string) $form->id,
                        'appointment' => (string) $appointment->id,
                    ])
                    : null,
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
        $doctorPhone = $this->normalizeString($entry->doctor?->user?->telefone ?? $entry->doctor?->user?->phone);
        $doctorEmail = $this->normalizeString($entry->doctor?->user?->email);
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
                'phone' => $doctorPhone,
                'email' => $doctorEmail,
            ],
            // Alias kept for compatibility with existing placeholders in defaults.
            'professional' => [
                'name' => $doctorName,
                'specialty' => $doctorSpecialty,
                'phone' => $doctorPhone,
                'email' => $doctorEmail,
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
            'online' => [
                'is_online' => false,
                'meeting_link' => null,
                'meeting_app' => null,
                'general_instructions' => null,
                'patient_instructions' => null,
                'instructions_sent' => false,
                'instructions_sent_email_at' => null,
                'instructions_sent_whatsapp_at' => null,
            ],
            'links' => [
                'appointment_confirm' => null,
                'appointment_cancel' => null,
                'appointment_details' => null,
                'online_appointment_details' => null,
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

    public function buildForFormResponse(FormResponse $formResponse): array
    {
        $formResponse->loadMissing([
            'form',
            'patient',
            'appointment.patient',
            'appointment.doctor.user',
            'appointment.doctor.specialties',
            'appointment.calendar.doctor.user',
            'appointment.specialty',
            'appointment.type',
        ]);

        $tenant = $this->resolveCurrentTenant();
        $slug = $this->resolveTenantSlug($tenant);
        $timezone = $this->resolveTimezone();
        $submittedAt = $this->toCarbon($formResponse->submitted_at, $timezone);

        $context = $formResponse->appointment
            ? $this->buildForAppointment($formResponse->appointment)
            : [
                'clinic' => $this->buildClinicContext($tenant, $slug),
                'patient' => [
                    'name' => $this->normalizeString($formResponse->patient?->full_name),
                    'phone' => $this->normalizeString($formResponse->patient?->phone),
                    'email' => $this->normalizeString($formResponse->patient?->email),
                ],
                'doctor' => [
                    'name' => null,
                    'specialty' => null,
                    'phone' => null,
                    'email' => null,
                ],
                'professional' => [
                    'name' => null,
                    'specialty' => null,
                    'phone' => null,
                    'email' => null,
                ],
                'appointment' => [
                    'date' => null,
                    'time' => null,
                    'datetime' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'type' => null,
                    'mode' => null,
                    'status' => null,
                    'confirmation_expires_at' => null,
                ],
                'online' => [
                    'is_online' => false,
                    'meeting_link' => null,
                    'meeting_app' => null,
                    'general_instructions' => null,
                    'patient_instructions' => null,
                    'instructions_sent' => false,
                    'instructions_sent_email_at' => null,
                    'instructions_sent_whatsapp_at' => null,
                ],
                'links' => [
                    'appointment_confirm' => null,
                    'appointment_cancel' => null,
                    'appointment_details' => null,
                    'online_appointment_details' => null,
                    'waitlist_offer' => null,
                ],
                'waitlist' => [
                    'offer_expires_at' => null,
                    'status' => null,
                ],
            ];

        $context['form'] = [
            'id' => $formResponse->form_id ? (string) $formResponse->form_id : null,
            'name' => $this->normalizeString($formResponse->form?->name),
        ];
        $context['response'] = [
            'id' => (string) $formResponse->id,
            'submitted_at' => $this->formatDateTime($submittedAt),
        ];
        $context['links']['form_response'] = $this->buildTenantRoute('tenant.responses.show', $slug, [
            'id' => $formResponse->id,
        ]);

        return $context;
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

    private function buildTenantRoute(string $routeName, ?string $slug, array $parameters = []): ?string
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
