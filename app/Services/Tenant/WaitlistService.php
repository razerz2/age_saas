<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Models\Tenant\Doctor;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WaitlistService
{
    public const SLOT_STATUS_FREE = 'FREE';
    public const SLOT_STATUS_HOLD = 'HOLD';
    public const SLOT_STATUS_BUSY = 'BUSY';

    public function __construct(private readonly NotificationDispatcher $notificationDispatcher)
    {
    }

    /**
     * @param  array{
     *   doctor_id:string,
     *   patient_id:string,
     *   starts_at:mixed,
     *   ends_at:mixed
     * }  $payload
     * @return array{created:bool, entry:AppointmentWaitlistEntry, slot_status:string}
     *
     * @throws ValidationException
     */
    public function joinWaitlist(array $payload): array
    {
        $this->assertWaitlistEnabled();

        $tenantId = $this->currentTenantId();
        $doctorId = (string) $payload['doctor_id'];
        $patientId = (string) $payload['patient_id'];
        $startsAt = $this->normalizeDateTime($payload['starts_at']);
        $endsAt = $this->normalizeDateTime($payload['ends_at']);

        if ($endsAt->lte($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => 'O horário final deve ser maior que o horário inicial.',
            ]);
        }

        try {
            $result = DB::connection('tenant')->transaction(function () use ($tenantId, $doctorId, $patientId, $startsAt, $endsAt) {
                $slotStatus = $this->resolveSlotStatus($doctorId, $startsAt, $endsAt);
                if ($slotStatus === self::SLOT_STATUS_FREE) {
                    throw ValidationException::withMessages([
                        'slot' => 'Este horário está livre. Realize o agendamento normalmente.',
                    ]);
                }

                $allowWhenConfirmed = tenant_setting_bool('appointments.waitlist.allow_when_confirmed', true);
                if ($slotStatus === self::SLOT_STATUS_BUSY && !$allowWhenConfirmed) {
                    throw ValidationException::withMessages([
                        'slot' => 'A fila de espera para horários ocupados está desabilitada neste tenant.',
                    ]);
                }

                $slotStartsAt = $startsAt->format('Y-m-d H:i:s');
                $slotEndsAt = $endsAt->format('Y-m-d H:i:s');

                $existingEntry = AppointmentWaitlistEntry::query()
                    ->where('tenant_id', $tenantId)
                    ->where('doctor_id', $doctorId)
                    ->where('patient_id', $patientId)
                    ->where('starts_at', $slotStartsAt)
                    ->where('ends_at', $slotEndsAt)
                    ->lockForUpdate()
                    ->first();

                if ($existingEntry) {
                    if (in_array($existingEntry->status, [AppointmentWaitlistEntry::STATUS_WAITING, AppointmentWaitlistEntry::STATUS_OFFERED], true)) {
                        return [
                            'created' => false,
                            'entry' => $existingEntry,
                            'slot_status' => $slotStatus,
                        ];
                    }

                    throw ValidationException::withMessages([
                        'slot' => 'Já existe histórico de fila de espera para este paciente neste horário.',
                    ]);
                }

                $maxPerSlot = tenant_setting_nullable_int('appointments.waitlist.max_per_slot', null);
                if ($maxPerSlot !== null) {
                    $currentCount = AppointmentWaitlistEntry::query()
                        ->where('tenant_id', $tenantId)
                        ->where('doctor_id', $doctorId)
                        ->where('starts_at', $slotStartsAt)
                        ->where('ends_at', $slotEndsAt)
                        ->whereIn('status', [AppointmentWaitlistEntry::STATUS_WAITING, AppointmentWaitlistEntry::STATUS_OFFERED])
                        ->count();

                    if ($currentCount >= $maxPerSlot) {
                        throw ValidationException::withMessages([
                            'slot' => 'Este horário atingiu o limite máximo de pacientes na fila de espera.',
                        ]);
                    }
                }

                $entry = AppointmentWaitlistEntry::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'doctor_id' => $doctorId,
                    'patient_id' => $patientId,
                    'starts_at' => $slotStartsAt,
                    'ends_at' => $slotEndsAt,
                    'status' => AppointmentWaitlistEntry::STATUS_WAITING,
                    'offer_token' => null,
                    'offered_at' => null,
                    'offer_expires_at' => null,
                    'accepted_at' => null,
                ]);

                return [
                    'created' => true,
                    'entry' => $entry,
                    'slot_status' => $slotStatus,
                ];
            }, 3);

            if ($result['created'] === true) {
                $this->notificationDispatcher->dispatchWaitlist(
                    $result['entry'],
                    'waitlist.joined',
                    ['event' => 'waitlist_joined']
                );
            }

            return $result;
        } catch (QueryException $e) {
            // Em corrida de inserção, trata como idempotente se já existir WAITING/OFFERED.
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23000') {
                $existingEntry = AppointmentWaitlistEntry::query()
                    ->where('tenant_id', $tenantId)
                    ->where('doctor_id', $doctorId)
                    ->where('patient_id', $patientId)
                    ->where('starts_at', $startsAt->format('Y-m-d H:i:s'))
                    ->where('ends_at', $endsAt->format('Y-m-d H:i:s'))
                    ->whereIn('status', [AppointmentWaitlistEntry::STATUS_WAITING, AppointmentWaitlistEntry::STATUS_OFFERED])
                    ->first();

                if ($existingEntry) {
                    return [
                        'created' => false,
                        'entry' => $existingEntry,
                        'slot_status' => $this->resolveSlotStatus($doctorId, $startsAt, $endsAt),
                    ];
                }
            }

            throw $e;
        }
    }

    public function onSlotReleased(string $doctorId, $startsAt, $endsAt): ?AppointmentWaitlistEntry
    {
        if (!tenant_setting_bool('appointments.waitlist.enabled', false)) {
            return null;
        }

        return $this->offerNext($doctorId, $startsAt, $endsAt);
    }

    public function offerNext(string $doctorId, $startsAt, $endsAt): ?AppointmentWaitlistEntry
    {
        if (!tenant_setting_bool('appointments.waitlist.enabled', false)) {
            return null;
        }

        $tenantId = $this->currentTenantId();
        $slotStart = $this->normalizeDateTime($startsAt);
        $slotEnd = $this->normalizeDateTime($endsAt);
        $slotStartString = $slotStart->format('Y-m-d H:i:s');
        $slotEndString = $slotEnd->format('Y-m-d H:i:s');

        $entry = DB::connection('tenant')->transaction(function () use ($tenantId, $doctorId, $slotStart, $slotEnd, $slotStartString, $slotEndString) {
            if ($this->resolveSlotStatus($doctorId, $slotStart, $slotEnd) !== self::SLOT_STATUS_FREE) {
                return null;
            }

            $alreadyOffered = AppointmentWaitlistEntry::query()
                ->where('tenant_id', $tenantId)
                ->where('doctor_id', $doctorId)
                ->where('starts_at', $slotStartString)
                ->where('ends_at', $slotEndString)
                ->where('status', AppointmentWaitlistEntry::STATUS_OFFERED)
                ->lockForUpdate()
                ->exists();

            if ($alreadyOffered) {
                return null;
            }

            $nextEntry = AppointmentWaitlistEntry::query()
                ->where('tenant_id', $tenantId)
                ->where('doctor_id', $doctorId)
                ->where('starts_at', $slotStartString)
                ->where('ends_at', $slotEndString)
                ->where('status', AppointmentWaitlistEntry::STATUS_WAITING)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (!$nextEntry) {
                return null;
            }

            $offerTtlMinutes = max(1, tenant_setting_int('appointments.waitlist.offer_ttl_minutes', 15));
            $nextEntry->status = AppointmentWaitlistEntry::STATUS_OFFERED;
            $nextEntry->offer_token = $this->generateUniqueOfferToken();
            $nextEntry->offered_at = now();
            $nextEntry->offer_expires_at = now()->addMinutes($offerTtlMinutes);
            $nextEntry->save();

            return $nextEntry;
        }, 3);

        if ($entry) {
            $this->logOfferGenerated($entry);
        }

        return $entry;
    }

    /**
     * @return array{appointment:Appointment, entry:AppointmentWaitlistEntry}
     *
     * @throws ValidationException
     */
    public function acceptOfferByToken(string $token, string $origin = 'public'): array
    {
        $this->assertWaitlistEnabled();

        $result = DB::connection('tenant')->transaction(function () use ($token, $origin) {
            /** @var AppointmentWaitlistEntry|null $entry */
            $entry = AppointmentWaitlistEntry::query()
                ->where('offer_token', $token)
                ->lockForUpdate()
                ->first();

            if (!$entry) {
                throw ValidationException::withMessages([
                    'offer' => 'Oferta não encontrada.',
                ]);
            }

            if (!$entry->isOfferValid()) {
                if (
                    $entry->status === AppointmentWaitlistEntry::STATUS_OFFERED
                    && $entry->offer_expires_at !== null
                    && $entry->offer_expires_at->lte(now())
                ) {
                    $entry->status = AppointmentWaitlistEntry::STATUS_EXPIRED;
                    $entry->save();
                }

                throw ValidationException::withMessages([
                    'offer' => 'Esta oferta não está mais válida.',
                ]);
            }

            if ($this->resolveSlotStatus($entry->doctor_id, $entry->starts_at, $entry->ends_at) !== self::SLOT_STATUS_FREE) {
                throw ValidationException::withMessages([
                    'slot' => 'Este horário não está mais disponível.',
                ]);
            }

            $doctor = Doctor::query()->find($entry->doctor_id);
            if (!$doctor) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Médico não encontrado para esta oferta.',
                ]);
            }

            $calendar = $doctor->getPrimaryCalendar();
            if (!$calendar) {
                throw ValidationException::withMessages([
                    'calendar' => 'Não foi possível localizar um calendário para este médico.',
                ]);
            }

            $confirmationEnabled = tenant_setting_bool('appointments.confirmation.enabled', false);
            $confirmationTtlMinutes = max(1, tenant_setting_int('appointments.confirmation.ttl_minutes', 30));
            $defaultMode = (string) tenant_setting('appointments.default_appointment_mode', 'user_choice');
            $appointmentMode = $defaultMode === 'online' ? 'online' : 'presencial';

            $appointment = Appointment::query()->create([
                'id' => (string) Str::uuid(),
                'calendar_id' => $calendar->id,
                'doctor_id' => $entry->doctor_id,
                'appointment_type' => null,
                'patient_id' => $entry->patient_id,
                'specialty_id' => null,
                'starts_at' => $entry->starts_at,
                'ends_at' => $entry->ends_at,
                'status' => $confirmationEnabled ? 'pending_confirmation' : 'scheduled',
                'appointment_mode' => $appointmentMode,
                'origin' => $origin,
                'confirmation_token' => $this->generateUniqueAppointmentConfirmationToken(),
                'confirmation_expires_at' => $confirmationEnabled ? now()->addMinutes($confirmationTtlMinutes) : null,
                'confirmed_at' => $confirmationEnabled ? null : now(),
                'canceled_at' => null,
                'cancellation_reason' => null,
                'expired_at' => null,
                'notes' => null,
            ]);

            $entry->status = AppointmentWaitlistEntry::STATUS_ACCEPTED;
            $entry->accepted_at = now();
            $entry->save();

            return [
                'appointment' => $appointment,
                'entry' => $entry,
            ];
        }, 3);

        if ($result['appointment']->isHold()) {
            $this->notificationDispatcher->dispatchAppointment(
                $result['appointment'],
                'appointment.pending_confirmation',
                [
                    'event' => 'waitlist_offer_accepted_pending_confirmation',
                    'origin' => $origin,
                ]
            );
        }

        return $result;
    }

    /**
     * @return self::SLOT_STATUS_FREE|self::SLOT_STATUS_HOLD|self::SLOT_STATUS_BUSY
     */
    public function resolveSlotStatus(string $doctorId, $startsAt, $endsAt): string
    {
        $slotStart = $this->normalizeDateTime($startsAt);
        $slotEnd = $this->normalizeDateTime($endsAt);

        $appointments = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereIn('status', ['scheduled', 'rescheduled', 'pending_confirmation'])
            ->where('starts_at', '<', $slotEnd->format('Y-m-d H:i:s'))
            ->where('ends_at', '>', $slotStart->format('Y-m-d H:i:s'))
            ->get(['status']);

        if ($appointments->contains(fn (Appointment $appointment) => $appointment->status === 'pending_confirmation')) {
            return self::SLOT_STATUS_HOLD;
        }

        if ($appointments->contains(fn (Appointment $appointment) => in_array($appointment->status, ['scheduled', 'rescheduled'], true))) {
            return self::SLOT_STATUS_BUSY;
        }

        return self::SLOT_STATUS_FREE;
    }

    private function assertWaitlistEnabled(): void
    {
        if (!tenant_setting_bool('appointments.waitlist.enabled', false)) {
            throw ValidationException::withMessages([
                'waitlist' => 'A fila de espera está desabilitada para este tenant.',
            ]);
        }
    }

    private function currentTenantId(): string
    {
        $tenant = tenant();
        $tenantId = $tenant?->id;

        if (!$tenantId) {
            throw ValidationException::withMessages([
                'tenant' => 'Contexto de tenant não inicializado.',
            ]);
        }

        return (string) $tenantId;
    }

    private function normalizeDateTime($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        return Carbon::parse($value);
    }

    private function generateUniqueOfferToken(): string
    {
        do {
            $token = Str::random(64);
        } while (AppointmentWaitlistEntry::query()->where('offer_token', $token)->exists());

        return $token;
    }

    private function generateUniqueAppointmentConfirmationToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Appointment::query()->where('confirmation_token', $token)->exists());

        return $token;
    }

    private function logOfferGenerated(AppointmentWaitlistEntry $entry): void
    {
        $this->notificationDispatcher->dispatchWaitlist(
            $entry,
            'waitlist.offered',
            ['event' => 'waitlist_offer_created']
        );
    }
}
