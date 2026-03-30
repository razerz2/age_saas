<?php

namespace App\Services\Tenant\WhatsAppBot\Domain;

use App\Http\Requests\Tenant\StoreAppointmentRequest;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Patient;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\Scheduling\DoctorSlotFinder;
use App\Services\Tenant\WaitlistService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WhatsAppBotAppointmentService
{
    private ?Collection $specialtiesCache = null;

    /**
     * @var array<string, Collection<int, array{id:string,name:string,calendar_id:string,appointment_type_id:?string,duration_min:int}>>
     */
    private array $doctorsCache = [];

    public function __construct(
        private readonly DoctorSlotFinder $slotFinder,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly WaitlistService $waitlistService
    ) {
    }

    /**
     * @return Collection<int, array{id:string,name:string}>
     */
    public function listSpecialties(): Collection
    {
        if ($this->specialtiesCache instanceof Collection) {
            return $this->specialtiesCache;
        }

        $this->specialtiesCache = MedicalSpecialty::query()
            ->whereHas('doctors', function ($query): void {
                $this->applySchedulableDoctorFilters($query);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MedicalSpecialty $specialty): array => [
                'id' => (string) $specialty->id,
                'name' => (string) $specialty->name,
            ])
            ->values();

        return $this->specialtiesCache;
    }

    /**
     * @return Collection<int, array{id:string,name:string,calendar_id:string,appointment_type_id:?string,duration_min:int}>
     */
    public function listDoctors(?string $specialtyId = null): Collection
    {
        $cacheKey = trim((string) ($specialtyId ?? '')) ?: 'all';
        if (isset($this->doctorsCache[$cacheKey])) {
            return $this->doctorsCache[$cacheKey];
        }

        $query = Doctor::query()
            ->with(['user:id,name,name_full,status', 'appointmentTypes'])
            ->orderBy('id');

        $this->applySchedulableDoctorFilters($query);

        if ($specialtyId !== null && trim($specialtyId) !== '') {
            $query->whereHas('specialties', function ($specialtyQuery) use ($specialtyId): void {
                $specialtyQuery->where('medical_specialties.id', $specialtyId);
            });
        }

        $this->doctorsCache[$cacheKey] = $query->get()->map(function (Doctor $doctor): ?array {
            $calendar = $doctor->getPrimaryCalendar();
            if (!$calendar) {
                return null;
            }

            $appointmentType = $this->resolveDefaultAppointmentType($doctor);
            if (!$appointmentType) {
                return null;
            }

            $name = $this->resolveDoctorDisplayName($doctor);

            return [
                'id' => (string) $doctor->id,
                'name' => $name,
                'calendar_id' => (string) $calendar->id,
                'appointment_type_id' => (string) $appointmentType->id,
                'duration_min' => (int) ($appointmentType->duration_min ?? 30),
            ];
        })->filter()->values();

        return $this->doctorsCache[$cacheKey];
    }

    /**
     * @return Collection<int, array{starts_at:string,ends_at:string,label:string}>
     */
    public function listAvailableSlots(
        string $doctorId,
        CarbonInterface $date,
        int $durationMinutes
    ): Collection {
        $slots = $this->slotFinder->findAvailableSlots(
            doctorId: $doctorId,
            date: $date,
            durationMinutes: $durationMinutes
        );

        $timezone = $this->timezone();
        $now = Carbon::now($timezone);

        return $slots
            ->filter(function (array $slot) use ($now): bool {
                $startsAt = Carbon::instance($slot['starts_at']->toMutable());
                return $startsAt->gte($now);
            })
            ->map(function (array $slot): array {
                $startsAt = Carbon::instance($slot['starts_at']->toMutable());
                $endsAt = Carbon::instance($slot['ends_at']->toMutable());

                return [
                    'starts_at' => $startsAt->format('Y-m-d H:i:s'),
                    'ends_at' => $endsAt->format('Y-m-d H:i:s'),
                    'label' => $startsAt->format('H:i'),
                ];
            })
            ->values();
    }

    /**
     * @throws ValidationException
     */
    public function createAppointment(
        Patient $patient,
        string $doctorId,
        string $calendarId,
        ?string $specialtyId,
        ?string $appointmentTypeId,
        string $startsAt,
        string $endsAt
    ): Appointment {
        $this->assertNoDuplicateAppointment(
            patientId: (string) $patient->id,
            doctorId: $doctorId,
            startsAt: $startsAt
        );

        $this->assertSlotStillAvailable(
            doctorId: $doctorId,
            startsAt: $startsAt,
            endsAt: $endsAt
        );

        $payload = [
            'doctor_id' => $doctorId,
            'calendar_id' => $calendarId,
            'appointment_type' => $appointmentTypeId,
            'patient_id' => (string) $patient->id,
            'specialty_id' => $specialtyId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'appointment_mode' => 'presencial',
        ];

        $this->validateWithStoreAppointmentRequest($payload);

        $payload['id'] = (string) Str::uuid();
        $payload['confirmation_token'] = $this->generateUniqueConfirmationToken();
        $payload['origin'] = 'whatsapp_bot';

        $confirmationEnabled = tenant_setting_bool('appointments.confirmation.enabled', false);
        $confirmationTtlMinutes = max(1, tenant_setting_int('appointments.confirmation.ttl_minutes', 30));

        if ($confirmationEnabled) {
            $payload['status'] = 'pending_confirmation';
            $payload['confirmation_expires_at'] = now()->addMinutes($confirmationTtlMinutes);
            $payload['confirmed_at'] = null;
            $payload['expired_at'] = null;
            $payload['canceled_at'] = null;
            $payload['cancellation_reason'] = null;
        } else {
            $payload['status'] = 'scheduled';
            $payload['confirmed_at'] = now();
            $payload['confirmation_expires_at'] = null;
            $payload['expired_at'] = null;
        }

        $mode = (string) \App\Models\Tenant\TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        $payload['appointment_mode'] = in_array($mode, ['presencial', 'online'], true) ? $mode : 'presencial';

        $appointment = Appointment::query()->create($payload);

        if ($appointment->isHold()) {
            $this->notificationDispatcher->dispatchAppointment(
                $appointment,
                'appointment.pending_confirmation',
                [
                    'event' => 'appointment_created_pending_confirmation',
                    'origin' => 'whatsapp_bot',
                    'suppress_channels' => ['whatsapp'],
                ]
            );
        }

        Log::info('whatsapp_bot.appointment.created', [
            'tenant_id' => (string) (tenant()?->id ?? ''),
            'appointment_id' => (string) $appointment->id,
            'patient_id' => (string) $appointment->patient_id,
            'doctor_id' => (string) $appointment->doctor_id,
            'starts_at' => (string) $appointment->starts_at,
            'status' => (string) $appointment->status,
        ]);

        return $appointment;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function listUpcomingAppointments(Patient $patient, int $limit = 5): Collection
    {
        return Appointment::query()
            ->with(['doctor.user'])
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['scheduled', 'rescheduled', 'pending_confirmation', 'confirmed'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function listCancelableAppointments(Patient $patient, int $limit = 10): Collection
    {
        return Appointment::query()
            ->with(['doctor.user'])
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['scheduled', 'rescheduled', 'pending_confirmation', 'confirmed'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    public function cancelAppointment(Appointment $appointment, ?string $reason = null): bool
    {
        if (in_array((string) $appointment->status, ['canceled', 'cancelled'], true)) {
            return false;
        }

        if (!tenant_setting_bool('appointments.allow_cancellation', true)) {
            throw ValidationException::withMessages([
                'appointment' => 'Cancelamento nao permitido para esta conta no momento.',
            ]);
        }

        $cancellationHours = max(0, tenant_setting_int('appointments.cancellation_hours', 24));
        $startsAt = $appointment->starts_at ? Carbon::parse((string) $appointment->starts_at) : null;

        if ($startsAt && $startsAt->lte(now()->addHours($cancellationHours))) {
            throw ValidationException::withMessages([
                'appointment' => sprintf(
                    'Cancelamento permitido apenas com pelo menos %d horas de antecedencia.',
                    $cancellationHours
                ),
            ]);
        }

        $appointment->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'cancellation_reason' => $reason ?: 'Cancelado via bot WhatsApp.',
            'confirmation_expires_at' => null,
            'expired_at' => null,
        ]);

        $this->notificationDispatcher->dispatchAppointment(
            $appointment,
            'appointment.canceled',
            [
                'event' => 'appointment_canceled_whatsapp_bot',
                'origin' => 'whatsapp_bot',
                'suppress_channels' => ['whatsapp'],
            ]
        );

        $this->waitlistService->onSlotReleased(
            (string) $appointment->doctor_id,
            (string) $appointment->starts_at,
            (string) $appointment->ends_at
        );

        Log::info('whatsapp_bot.appointment.canceled', [
            'tenant_id' => (string) (tenant()?->id ?? ''),
            'appointment_id' => (string) $appointment->id,
            'patient_id' => (string) $appointment->patient_id,
            'doctor_id' => (string) $appointment->doctor_id,
        ]);

        return true;
    }

    private function validateWithStoreAppointmentRequest(array $payload): void
    {
        $request = StoreAppointmentRequest::create('/', 'POST', $payload);
        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
        $request->withValidator($validator);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function resolveDefaultAppointmentType(Doctor $doctor): ?AppointmentType
    {
        $fromDoctor = $doctor->appointmentTypes
            ->where('is_active', true)
            ->sortBy('duration_min')
            ->first();

        if ($fromDoctor instanceof AppointmentType) {
            return $fromDoctor;
        }

        return AppointmentType::query()
            ->whereNull('doctor_id')
            ->where('is_active', true)
            ->orderBy('duration_min')
            ->first();
    }

    private function generateUniqueConfirmationToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Appointment::query()->where('confirmation_token', $token)->exists());

        return $token;
    }

    private function applySchedulableDoctorFilters($query): void
    {
        $query->whereHas('user', function ($userQuery): void {
            $userQuery->where('status', 'active');
        })->whereHas('calendars', function ($calendarQuery): void {
            $calendarQuery->where('is_active', true);
        })->whereHas('businessHours')
            ->whereHas('appointmentTypes', function ($appointmentTypeQuery): void {
                $appointmentTypeQuery->where('is_active', true);
            });
    }

    private function timezone(): string
    {
        return (string) tenant_setting('timezone', config('app.timezone', 'America/Campo_Grande'));
    }

    private function resolveDoctorDisplayName(Doctor $doctor): string
    {
        $candidates = [
            trim((string) ($doctor->user?->name_full ?? '')),
            trim((string) ($doctor->user?->name ?? '')),
            trim((string) ($doctor->crm_number ?? '')),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if ($this->looksTechnicalLabel($candidate)) {
                continue;
            }

            return $candidate;
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return 'Profissional';
    }

    private function looksTechnicalLabel(string $value): bool
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strtolower($value)) ?? '');
        if ($normalized === '') {
            return true;
        }

        $hasTechnicalToken = preg_match('/\b(dusk|teste?|test|seed)\b/i', $normalized) === 1;
        $hasLongNumericSuffix = preg_match('/\d{5,}/', $normalized) === 1;

        return $hasTechnicalToken && $hasLongNumericSuffix;
    }

    /**
     * @throws ValidationException
     */
    private function assertNoDuplicateAppointment(string $patientId, string $doctorId, string $startsAt): void
    {
        $exists = Appointment::query()
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->where('starts_at', $startsAt)
            ->whereIn('status', ['scheduled', 'rescheduled', 'pending_confirmation', 'confirmed'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'starts_at' => 'Voce ja possui um agendamento ativo neste horario.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertSlotStillAvailable(string $doctorId, string $startsAt, string $endsAt): void
    {
        try {
            $start = Carbon::parse($startsAt, $this->timezone());
            $end = Carbon::parse($endsAt, $this->timezone());
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'starts_at' => 'Horario selecionado invalido. Escolha outro horario.',
            ]);
        }
        $durationMinutes = max(1, $start->diffInMinutes($end));

        $hasSlot = $this->slotFinder->findAvailableSlots(
            doctorId: $doctorId,
            date: $start->copy()->startOfDay(),
            durationMinutes: $durationMinutes
        )->contains(function (array $slot) use ($start, $end): bool {
            $slotStart = Carbon::instance($slot['starts_at']->toMutable());
            $slotEnd = Carbon::instance($slot['ends_at']->toMutable());

            return $slotStart->equalTo($start) && $slotEnd->equalTo($end);
        });

        if (!$hasSlot) {
            throw ValidationException::withMessages([
                'starts_at' => 'Horario selecionado nao esta mais disponivel. Escolha outro horario.',
            ]);
        }
    }
}
