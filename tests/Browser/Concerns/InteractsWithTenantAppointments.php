<?php

namespace Tests\Browser\Concerns;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Str;
use RuntimeException;

trait InteractsWithTenantAppointments
{
    use InteractsWithTenantPatients;

    /**
     * @return array{
     *   doctor: array{id: string, name: string, calendar_id: string, appointment_type_id: string, specialty_id: string},
     *   patient: array{id: string, full_name: string, cpf: string}
     * }
     */
    protected function createControlledAppointmentDependencies(string $seed): array
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        return [
            'doctor' => $this->createSchedulableDoctorTarget($seed),
            'patient' => $this->createControlledPatientTarget(
                seed: $seed,
                namePrefix: 'Paciente Agenda Base',
                testTag: 'dusk_appointment_patient'
            ),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array{
     *   id: string,
     *   patient_id: string,
     *   patient_name: string,
     *   doctor_id: string,
     *   doctor_name: string,
     *   starts_at: string,
     *   ends_at: string,
     *   date_label: string,
     *   time_label: string,
     *   notes: string,
     *   status: string
     * }
     */
    protected function createControlledAppointmentTarget(string $seed, array $attributes = []): array
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        $dependencies = $attributes['dependencies'] ?? $this->createControlledAppointmentDependencies($seed);
        $doctor = $dependencies['doctor'];
        $patient = $dependencies['patient'];

        $startsAt = $this->normalizeAppointmentDateTime(
            $attributes['starts_at'] ?? CarbonImmutable::now('America/Campo_Grande')->addDay()->setTime(9, 0, 0)
        );
        $durationMinutes = (int) ($attributes['duration_min'] ?? 30);
        $endsAt = $this->normalizeAppointmentDateTime(
            $attributes['ends_at'] ?? $startsAt->addMinutes(max(5, $durationMinutes))
        );

        $status = (string) ($attributes['status'] ?? 'scheduled');
        $notes = (string) ($attributes['notes'] ?? sprintf('Agendamento Dusk %s', $seed));
        $testTag = (string) ($attributes['test_tag'] ?? 'dusk_appointment');

        $appointment = Appointment::query()->create([
            'id' => (string) Str::uuid(),
            'calendar_id' => $doctor['calendar_id'],
            'doctor_id' => $doctor['id'],
            'appointment_type' => $doctor['appointment_type_id'],
            'patient_id' => $patient['id'],
            'specialty_id' => $attributes['specialty_id'] ?? $doctor['specialty_id'] ?? null,
            'starts_at' => $startsAt->setTimezone('UTC'),
            'ends_at' => $endsAt->setTimezone('UTC'),
            'status' => $status,
            'appointment_mode' => (string) ($attributes['appointment_mode'] ?? 'presencial'),
            'origin' => (string) ($attributes['origin'] ?? 'internal'),
            'notes' => $notes,
            'is_test' => true,
            'test_tag' => $testTag,
        ]);

        return [
            'id' => (string) $appointment->id,
            'patient_id' => (string) $patient['id'],
            'patient_name' => (string) $patient['full_name'],
            'doctor_id' => (string) $doctor['id'],
            'doctor_name' => (string) $doctor['name'],
            'starts_at' => (string) $appointment->starts_at?->format('Y-m-d H:i:s'),
            'ends_at' => (string) $appointment->ends_at?->format('Y-m-d H:i:s'),
            'date_label' => (string) $appointment->starts_at?->format('d/m/Y'),
            'time_label' => (string) $appointment->starts_at?->format('H:i'),
            'notes' => $notes,
            'status' => (string) $appointment->status,
        ];
    }

    protected function tenantAppointmentExists(string $appointmentId): bool
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        return Appointment::query()->whereKey($appointmentId)->exists();
    }

    protected function tenantAppointmentStatus(string $appointmentId): ?string
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        return Appointment::query()->whereKey($appointmentId)->value('status');
    }

    protected function tenantAppointmentIdByNotes(string $notes): ?string
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        return Appointment::query()
            ->where('notes', $notes)
            ->latest('created_at')
            ->value('id');
    }

    protected function nextAppointmentDateString(int $daysAhead = 1): string
    {
        return CarbonImmutable::now('America/Campo_Grande')
            ->addDays(max(0, $daysAhead))
            ->format('Y-m-d');
    }

    /**
     * @return array{id: string, name: string, calendar_id: string, appointment_type_id: string, specialty_id: string}
     */
    private function createSchedulableDoctorTarget(string $seed): array
    {
        $tenant = $this->resolveTenantForAppointmentTests();
        $tenant->makeCurrent();

        $suffix = substr(preg_replace('/\D+/', '', $seed) ?: $seed, -8);
        $doctorName = sprintf('Medico Dusk %s', $suffix);

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $doctorName,
            'name_full' => $doctorName,
            'telefone' => sprintf('6599%s', str_pad(substr($suffix, -6), 6, '0', STR_PAD_LEFT)),
            'email' => sprintf('dusk.doctor.%s@tenant.test', $suffix),
            'password' => 'password',
            'is_doctor' => true,
            'status' => 'active',
            'role' => 'doctor',
        ]);

        $doctor = Doctor::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'crm_number' => sprintf('CRM%s', $suffix),
            'crm_state' => 'MT',
            'registration_value' => sprintf('REG-%s', $suffix),
        ]);

        $specialty = MedicalSpecialty::query()->create([
            'id' => (string) Str::uuid(),
            'name' => sprintf('Especialidade Dusk %s', $suffix),
            'code' => sprintf('DUSK-%s', $suffix),
            'label_singular' => 'Especialidade',
            'label_plural' => 'Especialidades',
            'registration_label' => 'Registro',
        ]);

        $doctor->specialties()->syncWithoutDetaching([$specialty->id]);

        $calendar = Calendar::query()->create([
            'id' => (string) Str::uuid(),
            'doctor_id' => $doctor->id,
            'name' => sprintf('Agenda Dusk %s', $suffix),
            'is_active' => true,
        ]);

        for ($weekday = 0; $weekday <= 6; $weekday++) {
            BusinessHour::query()->create([
                'id' => (string) Str::uuid(),
                'doctor_id' => $doctor->id,
                'weekday' => $weekday,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'break_start_time' => null,
                'break_end_time' => null,
            ]);
        }

        $appointmentType = AppointmentType::query()->create([
            'id' => (string) Str::uuid(),
            'doctor_id' => $doctor->id,
            'name' => sprintf('Consulta Dusk %s', $suffix),
            'duration_min' => 30,
            'is_active' => true,
        ]);

        return [
            'id' => (string) $doctor->id,
            'name' => $doctorName,
            'calendar_id' => (string) $calendar->id,
            'appointment_type_id' => (string) $appointmentType->id,
            'specialty_id' => (string) $specialty->id,
        ];
    }

    private function resolveTenantForAppointmentTests(): PlatformTenant
    {
        $context = $this->tenantTestContext();

        $tenant = PlatformTenant::query()
            ->where('subdomain', $context->slug)
            ->first();

        if (! $tenant instanceof PlatformTenant) {
            throw new RuntimeException(sprintf('Tenant "%s" nao encontrado para setup dos testes de agendamento.', $context->slug));
        }

        return $tenant;
    }

    private function normalizeAppointmentDateTime(DateTimeInterface|string $dateTime): CarbonImmutable
    {
        if ($dateTime instanceof DateTimeInterface) {
            return CarbonImmutable::instance($dateTime)->setTimezone('America/Campo_Grande');
        }

        return CarbonImmutable::parse($dateTime, 'America/Campo_Grande');
    }
}
