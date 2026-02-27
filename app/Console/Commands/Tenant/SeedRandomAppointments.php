<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Patient;
use App\Services\Tenant\Scheduling\DoctorSlotFinder;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class SeedRandomAppointments extends Command
{
    protected $signature = 'tenant:appointments:seed-random
                            {--tenant= : Slug (subdomain) de um tenant especifico}
                            {--all-tenants : Executa em todos os tenants}
                            {--count=50 : Quantidade de agendamentos por tenant}
                            {--days=14 : Janela em dias para sorteio}
                            {--start-date= : Data inicial (YYYY-MM-DD)}
                            {--end-date= : Data final (YYYY-MM-DD)}
                            {--duration=30 : Duracao fallback em minutos}
                            {--tag=test : Tag de teste para marcacao}
                            {--dry-run : Simula sem gravar}
                            {--force : Permite execucao em producao}';

    protected $description = 'Gera agendamentos ficticios aleatorios em um periodo para tenants.';

    public function __construct(private readonly DoctorSlotFinder $slotFinder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Execucao bloqueada em producao. Use --force para confirmar.');
            return self::FAILURE;
        }

        $count = max(0, (int) $this->option('count'));
        if ($count < 1) {
            $this->error('Informe --count com valor >= 1.');
            return self::FAILURE;
        }

        $durationFallback = max(5, (int) $this->option('duration'));
        $tag = trim((string) $this->option('tag')) ?: 'test';
        $dryRun = (bool) $this->option('dry-run');

        $range = $this->resolveDateRange(
            $this->option('start-date'),
            $this->option('end-date'),
            (int) $this->option('days')
        );
        if (!$range) {
            return self::FAILURE;
        }

        $tenants = $this->resolveTenants((string) $this->option('tenant'), (bool) $this->option('all-tenants'));
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $globalStart = microtime(true);
        $rows = [];

        foreach ($tenants as $tenant) {
            $tenantStart = microtime(true);
            $stats = [
                'requested' => $count,
                'attempts' => 0,
                'created' => 0,
                'skipped_no_slot' => 0,
                'skipped_no_doctor' => 0,
                'skipped_no_patient' => 0,
                'errors' => 0,
            ];
            $message = '-';

            try {
                $tenant->makeCurrent();
                $stats = $this->seedForTenant(
                    $count,
                    $durationFallback,
                    $tag,
                    $range['start'],
                    $range['end'],
                    $dryRun
                );
            } catch (Throwable $exception) {
                $stats['errors']++;
                $message = $exception->getMessage();
                $this->error("[{$tenant->subdomain}] falha: {$message}");
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $rows[] = [
                $tenant->subdomain ?: $tenant->id,
                (string) $stats['requested'],
                (string) $stats['created'],
                (string) $stats['attempts'],
                (string) $stats['skipped_no_slot'],
                (string) $stats['skipped_no_doctor'],
                (string) $stats['skipped_no_patient'],
                (string) $stats['errors'],
                number_format(microtime(true) - $tenantStart, 2) . 's',
                $message,
            ];
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Solicitados', 'Criados', 'Tentativas', 'NoSlot', 'NoDoctor', 'NoPatient', 'Erros', 'Tempo', 'Detalhes'],
            $rows
        );
        $this->info('Tempo total: ' . number_format(microtime(true) - $globalStart, 2) . 's');

        return self::SUCCESS;
    }

    /**
     * @return array{requested:int,attempts:int,created:int,skipped_no_slot:int,skipped_no_doctor:int,skipped_no_patient:int,errors:int}
     */
    private function seedForTenant(
        int $count,
        int $durationFallback,
        string $tag,
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        bool $dryRun
    ): array {
        if (!Schema::connection('tenant')->hasTable('appointments')) {
            throw new \RuntimeException('Tabela appointments nao encontrada neste tenant.');
        }

        $columns = Schema::connection('tenant')->getColumnListing('appointments');
        if (!$this->hasAnyTestMarkerColumn($columns)) {
            throw new \RuntimeException(
                'Sem coluna para marcar testes em appointments (is_test/test_tag/metadata/notes). Rode migrations tenant.'
            );
        }

        $doctors = Doctor::query()
            ->with([
                'appointmentTypes' => fn ($query) => $query->where('is_active', true),
                'specialties',
            ])
            ->whereHas('calendars')
            ->whereHas('businessHours')
            ->get();

        $patients = Patient::query()->pluck('id');
        $allSpecialtyIds = MedicalSpecialty::query()->pluck('id');

        $stats = [
            'requested' => $count,
            'attempts' => 0,
            'created' => 0,
            'skipped_no_slot' => 0,
            'skipped_no_doctor' => 0,
            'skipped_no_patient' => 0,
            'errors' => 0,
        ];

        /** @var array<string, array<int, array{starts_at:string, ends_at:string}>> $reservedSlots */
        $reservedSlots = [];
        $rangeDays = max(0, $startDate->diffInDays($endDate));

        for ($i = 0; $i < $count; $i++) {
            $stats['attempts']++;

            if ($doctors->isEmpty()) {
                $stats['skipped_no_doctor'] += ($count - $i);
                break;
            }

            if ($patients->isEmpty()) {
                $stats['skipped_no_patient'] += ($count - $i);
                break;
            }

            $doctor = $doctors->random();
            $patientId = (string) $patients->random();

            $offset = $rangeDays > 0 ? random_int(0, $rangeDays) : 0;
            $targetDate = $startDate->addDays($offset);

            /** @var AppointmentType|null $appointmentType */
            $appointmentType = $doctor->appointmentTypes->isNotEmpty()
                ? $doctor->appointmentTypes->random()
                : null;
            $duration = $appointmentType?->duration_min && $appointmentType->duration_min > 0
                ? (int) $appointmentType->duration_min
                : $durationFallback;

            $key = $doctor->id . '|' . $targetDate->toDateString();
            $reserved = $reservedSlots[$key] ?? [];

            $slots = $this->slotFinder->findAvailableSlots(
                (string) $doctor->id,
                $targetDate,
                $duration,
                $reserved
            );

            if ($slots->isEmpty()) {
                $stats['skipped_no_slot']++;
                continue;
            }

            $slot = $slots->random();
            $specialtyId = $this->resolveSpecialtyId($doctor, $allSpecialtyIds);
            $payload = $this->buildAppointmentPayload(
                $columns,
                $doctor->id,
                (string) $slot['calendar_id'],
                $patientId,
                $appointmentType?->id,
                $specialtyId,
                $slot['starts_at'],
                $slot['ends_at'],
                $tag
            );

            try {
                if (!$dryRun) {
                    Appointment::query()->create($payload);
                }

                $stats['created']++;
                $reservedSlots[$key][] = [
                    'starts_at' => $slot['starts_at']->toDateTimeString(),
                    'ends_at' => $slot['ends_at']->toDateTimeString(),
                ];
            } catch (Throwable $exception) {
                $stats['errors']++;
                $this->warn('Falha ao criar agendamento de teste: ' . $exception->getMessage());
            }
        }

        return $stats;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function buildAppointmentPayload(
        array $columns,
        string $doctorId,
        string $calendarId,
        string $patientId,
        ?string $appointmentTypeId,
        ?string $specialtyId,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        string $tag
    ): array {
        $confirmationEnabled = tenant_setting_bool('appointments.confirmation.enabled', false);
        $confirmationTtl = max(1, tenant_setting_int('appointments.confirmation.ttl_minutes', 30));
        $modeSetting = (string) tenant_setting('appointments.default_appointment_mode', 'user_choice');
        $appointmentMode = $modeSetting === 'online' ? 'online' : 'presencial';

        $payload = [
            'id' => (string) Str::uuid(),
            'calendar_id' => $calendarId,
            'doctor_id' => $doctorId,
            'appointment_type' => $appointmentTypeId,
            'patient_id' => $patientId,
            'specialty_id' => $specialtyId,
            'starts_at' => $startsAt->toDateTimeString(),
            'ends_at' => $endsAt->toDateTimeString(),
            'status' => $confirmationEnabled ? 'pending_confirmation' : 'scheduled',
            'appointment_mode' => $appointmentMode,
            'origin' => 'internal',
            'confirmation_expires_at' => $confirmationEnabled ? now()->addMinutes($confirmationTtl) : null,
            'confirmed_at' => $confirmationEnabled ? null : now(),
            'canceled_at' => null,
            'expired_at' => null,
            'cancellation_reason' => null,
            'confirmation_token' => $confirmationEnabled ? $this->generateUniqueConfirmationToken() : null,
            'notes' => null,
        ];

        $this->applyTestMarker($payload, $columns, $tag);

        return $payload;
    }

    /**
     * @param  Collection<int, string>  $allSpecialtyIds
     */
    private function resolveSpecialtyId(Doctor $doctor, Collection $allSpecialtyIds): ?string
    {
        if ($doctor->specialties->isNotEmpty()) {
            return (string) $doctor->specialties->random()->id;
        }

        if ($allSpecialtyIds->isNotEmpty()) {
            return (string) $allSpecialtyIds->random();
        }

        return null;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $payload
     */
    private function applyTestMarker(array &$payload, array $columns, string $tag): void
    {
        if (in_array('is_test', $columns, true)) {
            $payload['is_test'] = true;
        }

        if (in_array('test_tag', $columns, true)) {
            $payload['test_tag'] = $tag;
            return;
        }

        if (in_array('metadata', $columns, true)) {
            $payload['metadata'] = json_encode([
                'is_test' => true,
                'test_tag' => $tag,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (in_array('notes', $columns, true)) {
            $payload['notes'] = trim((string) ($payload['notes'] ?? '') . ' [test_appointment:' . $tag . ']');
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function hasAnyTestMarkerColumn(array $columns): bool
    {
        foreach (['is_test', 'test_tag', 'metadata', 'notes'] as $column) {
            if (in_array($column, $columns, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{start:CarbonImmutable, end:CarbonImmutable}|null
     */
    private function resolveDateRange(mixed $startDate, mixed $endDate, int $days): ?array
    {
        try {
            $start = is_string($startDate) && trim($startDate) !== ''
                ? CarbonImmutable::parse($startDate)->startOfDay()
                : CarbonImmutable::today()->startOfDay();

            if (is_string($endDate) && trim($endDate) !== '') {
                $end = CarbonImmutable::parse($endDate)->startOfDay();
            } else {
                $window = max(1, $days);
                $end = $start->addDays($window - 1)->startOfDay();
            }
        } catch (Throwable) {
            $this->error('Datas invalidas. Use formato YYYY-MM-DD.');
            return null;
        }

        if ($end->lt($start)) {
            $this->error('--end-date nao pode ser anterior a --start-date.');
            return null;
        }

        return ['start' => $start, 'end' => $end];
    }

    private function resolveTenants(string $tenantSlug, bool $allTenants): Collection
    {
        if ($tenantSlug !== '' && $allTenants) {
            $this->error('Use apenas uma opcao: --tenant ou --all-tenants.');
            return collect();
        }

        if ($tenantSlug === '' && !$allTenants) {
            $this->error('Informe --tenant=<slug> ou --all-tenants.');
            return collect();
        }

        if ($allTenants) {
            $tenants = PlatformTenant::query()->orderBy('subdomain')->get();

            if ($tenants->isEmpty()) {
                $this->warn('Nenhum tenant encontrado.');
            }

            return $tenants;
        }

        $tenantQuery = PlatformTenant::query()->where('subdomain', $tenantSlug);
        if (Str::isUuid($tenantSlug)) {
            $tenantQuery->orWhere('id', $tenantSlug);
        }

        $tenant = $tenantQuery->first();
        if (!$tenant) {
            $this->error("Tenant nao encontrado: {$tenantSlug}");
            return collect();
        }

        return collect([$tenant]);
    }

    private function generateUniqueConfirmationToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Appointment::query()->where('confirmation_token', $token)->exists());

        return $token;
    }
}
