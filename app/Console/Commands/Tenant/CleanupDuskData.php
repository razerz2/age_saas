<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Patient;
use App\Models\Tenant\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class CleanupDuskData extends Command
{
    protected $signature = 'tenant:cleanup-dusk-data
                            {--tenant= : Slug (subdomain) ou UUID do tenant}
                            {--all-tenants : Executa em todos os tenants}
                            {--dry-run : Apenas simula}
                            {--apply : Executa remocao de fato}
                            {--force : Permite execucao em producao}';

    protected $description = 'Audita e limpa massa tecnica Dusk (users, doctors, patients, specialties, appointments e dependencias).';

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Execucao bloqueada em producao. Use --force para confirmar.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run') || !(bool) $this->option('apply');
        $tenants = $this->resolveTenants((string) $this->option('tenant'), (bool) $this->option('all-tenants'));
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $plans = [];
        $previewRows = [];

        foreach ($tenants as $tenant) {
            $plan = $this->planTenant($tenant);
            $plans[(string) $tenant->id] = $plan;

            $previewRows[] = [
                (string) ($tenant->subdomain ?: $tenant->id),
                (string) $plan['dusk_counts']['users'],
                (string) $plan['dusk_counts']['doctors'],
                (string) $plan['dusk_counts']['patients'],
                (string) $plan['dusk_counts']['specialties'],
                (string) $plan['dusk_counts']['appointments'],
                $plan['error'] ? 'erro' : 'ok',
                $plan['error'] ?: $plan['criteria'],
            ];
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Users', 'Doctors', 'Patients', 'Specialties', 'Appointments', 'Status', 'Criterios'],
            $previewRows
        );

        foreach ($tenants as $tenant) {
            $plan = $plans[(string) $tenant->id];
            if ($plan['error']) {
                continue;
            }

            $this->line(sprintf(
                '[%s] Dashboard(raw) => consultas_mes=%d | pacientes=%d | profissionais=%d | especialidades=%d',
                $tenant->subdomain ?: $tenant->id,
                $plan['dashboard_counts']['consultas_periodo_mes'],
                $plan['dashboard_counts']['pacientes_ativos'],
                $plan['dashboard_counts']['profissionais'],
                $plan['dashboard_counts']['especialidades']
            ));

            $this->line(sprintf(
                '[%s] Totais(base) => users=%d, doctors=%d, patients=%d, specialties=%d, appointments=%d, appointment_types=%d, calendars=%d, business_hours=%d',
                $tenant->subdomain ?: $tenant->id,
                $plan['totals']['users'],
                $plan['totals']['doctors'],
                $plan['totals']['patients'],
                $plan['totals']['specialties'],
                $plan['totals']['appointments'],
                $plan['totals']['appointment_types'],
                $plan['totals']['calendars'],
                $plan['totals']['business_hours']
            ));
        }

        if ($dryRun) {
            $this->info('Dry-run: nenhuma remocao foi executada. Use --apply para executar.');
            return self::SUCCESS;
        }

        $summaryRows = [];
        foreach ($tenants as $tenant) {
            $plan = $plans[(string) $tenant->id] ?? null;
            $result = $this->cleanupTenant($tenant, $plan);
            $after = $this->planTenant($tenant);

            $summaryRows[] = [
                (string) ($tenant->subdomain ?: $tenant->id),
                (string) $result['deleted']['users'],
                (string) $result['deleted']['doctors'],
                (string) $result['deleted']['patients'],
                (string) $result['deleted']['specialties'],
                (string) $result['deleted']['appointments'],
                (string) $after['dusk_counts']['users'] . '/' . (string) $after['dusk_counts']['doctors'] . '/' . (string) $after['dusk_counts']['patients'] . '/' . (string) $after['dusk_counts']['specialties'] . '/' . (string) $after['dusk_counts']['appointments'],
                $result['error'] ? 'erro' : 'ok',
                $result['error'] ?: 'remocao concluida',
            ];
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Users', 'Doctors', 'Patients', 'Specialties', 'Appointments', 'Remanescente(U/D/P/S/A)', 'Status', 'Detalhes'],
            $summaryRows
        );

        return self::SUCCESS;
    }

    private function planTenant(PlatformTenant $tenant): array
    {
        $plan = [
            'criteria' => 'users(email dusk.doctor.*@tenant.test), patients(is_test/test_tag/nome Dusk), specialties(codigo DUSK-*), appointments(is_test/test_tag ou relacionadas)',
            'totals' => [
                'users' => 0,
                'doctors' => 0,
                'patients' => 0,
                'specialties' => 0,
                'appointments' => 0,
                'appointment_types' => 0,
                'calendars' => 0,
                'business_hours' => 0,
            ],
            'dashboard_counts' => [
                'consultas_periodo_mes' => 0,
                'pacientes_ativos' => 0,
                'profissionais' => 0,
                'especialidades' => 0,
            ],
            'dusk_counts' => [
                'users' => 0,
                'doctors' => 0,
                'patients' => 0,
                'specialties' => 0,
                'appointments' => 0,
                'appointment_types' => 0,
                'calendars' => 0,
                'business_hours' => 0,
            ],
            'ids' => [
                'users' => [],
                'doctors' => [],
                'patients' => [],
                'specialties' => [],
                'appointments' => [],
            ],
            'error' => null,
        ];

        try {
            $tenant->makeCurrent();

            $plan['totals'] = [
                'users' => User::query()->count(),
                'doctors' => Doctor::query()->count(),
                'patients' => Patient::query()->count(),
                'specialties' => MedicalSpecialty::query()->count(),
                'appointments' => Appointment::query()->count(),
                'appointment_types' => AppointmentType::query()->count(),
                'calendars' => Calendar::query()->count(),
                'business_hours' => BusinessHour::query()->count(),
            ];

            $monthStart = CarbonImmutable::now()->startOfMonth();
            $monthEnd = CarbonImmutable::now()->endOfMonth();
            $plan['dashboard_counts'] = [
                'consultas_periodo_mes' => Appointment::query()->whereBetween('starts_at', [$monthStart, $monthEnd])->count(),
                'pacientes_ativos' => Patient::query()->count(),
                'profissionais' => Doctor::query()->count(),
                'especialidades' => MedicalSpecialty::query()->count(),
            ];

            $userIds = User::query()
                ->where(function ($query): void {
                    $query->where('email', 'like', 'dusk.doctor.%@tenant.test')
                        ->orWhere(function ($nameQuery): void {
                            $nameQuery->where(function ($inner): void {
                                $inner->where('name', 'like', 'Medico Dusk %')
                                    ->orWhere('name_full', 'like', 'Medico Dusk %');
                            })->where('email', 'like', '%@tenant.test');
                        });
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $doctorIds = Doctor::query()
                ->where(function ($query) use ($userIds): void {
                    if (!empty($userIds)) {
                        $query->whereIn('user_id', $userIds);
                    }

                    $query->orWhereHas('user', function ($userQuery): void {
                        $userQuery->where('name', 'like', 'Medico Dusk %')
                            ->orWhere('name_full', 'like', 'Medico Dusk %')
                            ->orWhere('email', 'like', 'dusk.doctor.%@tenant.test');
                    });
                })
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $specialtyIds = MedicalSpecialty::query()
                ->where('code', 'like', 'DUSK-%')
                ->orWhere('name', 'like', 'Especialidade Dusk %')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $patientIds = $this->queryDuskPatients()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $appointmentIds = $this->queryDuskAppointments($doctorIds, $patientIds, $specialtyIds)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $plan['ids'] = [
                'users' => $userIds,
                'doctors' => $doctorIds,
                'patients' => $patientIds,
                'specialties' => $specialtyIds,
                'appointments' => $appointmentIds,
            ];

            $plan['dusk_counts'] = [
                'users' => count($userIds),
                'doctors' => count($doctorIds),
                'patients' => count($patientIds),
                'specialties' => count($specialtyIds),
                'appointments' => count($appointmentIds),
                'appointment_types' => empty($doctorIds) ? 0 : AppointmentType::query()->whereIn('doctor_id', $doctorIds)->count(),
                'calendars' => empty($doctorIds) ? 0 : Calendar::query()->whereIn('doctor_id', $doctorIds)->count(),
                'business_hours' => empty($doctorIds) ? 0 : BusinessHour::query()->whereIn('doctor_id', $doctorIds)->count(),
            ];
        } catch (Throwable $exception) {
            $plan['error'] = $exception->getMessage();
        } finally {
            SpatieTenant::forgetCurrent();
        }

        return $plan;
    }

    private function cleanupTenant(PlatformTenant $tenant, ?array $plan): array
    {
        $result = [
            'deleted' => [
                'users' => 0,
                'doctors' => 0,
                'patients' => 0,
                'specialties' => 0,
                'appointments' => 0,
            ],
            'error' => null,
        ];

        if (!$plan || !empty($plan['error'])) {
            $result['error'] = $plan['error'] ?? 'Falha no planejamento.';
            return $result;
        }

        try {
            $tenant->makeCurrent();

            $userIds = $plan['ids']['users'] ?? [];
            $doctorIds = $plan['ids']['doctors'] ?? [];
            $patientIds = $plan['ids']['patients'] ?? [];
            $specialtyIds = $plan['ids']['specialties'] ?? [];
            $appointmentIds = $plan['ids']['appointments'] ?? [];

            DB::connection('tenant')->transaction(function () use (&$result, $userIds, $doctorIds, $patientIds, $specialtyIds, $appointmentIds): void {
                if (!empty($appointmentIds)) {
                    if (Schema::connection('tenant')->hasTable('financial_charges')) {
                        DB::connection('tenant')->table('financial_charges')
                            ->whereIn('appointment_id', $appointmentIds)
                            ->update(['appointment_id' => null]);
                    }

                    if (Schema::connection('tenant')->hasTable('form_responses')) {
                        DB::connection('tenant')->table('form_responses')
                            ->whereIn('appointment_id', $appointmentIds)
                            ->update(['appointment_id' => null]);
                    }

                    if (Schema::connection('tenant')->hasTable('online_appointment_instructions')) {
                        DB::connection('tenant')->table('online_appointment_instructions')
                            ->whereIn('appointment_id', $appointmentIds)
                            ->delete();
                    }

                    if (Schema::connection('tenant')->hasTable('calendar_sync_state')) {
                        DB::connection('tenant')->table('calendar_sync_state')
                            ->whereIn('appointment_id', $appointmentIds)
                            ->delete();
                    }

                    $result['deleted']['appointments'] = Appointment::query()
                        ->whereIn('id', $appointmentIds)
                        ->delete();
                }

                if (!empty($patientIds)) {
                    if (Schema::connection('tenant')->hasTable('form_responses')) {
                        DB::connection('tenant')->table('form_responses')
                            ->whereIn('patient_id', $patientIds)
                            ->delete();
                    }

                    if (Schema::connection('tenant')->hasTable('appointment_waitlist_entries')) {
                        DB::connection('tenant')->table('appointment_waitlist_entries')
                            ->whereIn('patient_id', $patientIds)
                            ->delete();
                    }

                    $result['deleted']['patients'] = Patient::query()
                        ->whereIn('id', $patientIds)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('forms')) {
                    if (!empty($doctorIds)) {
                        DB::connection('tenant')->table('forms')
                            ->whereIn('doctor_id', $doctorIds)
                            ->update(['doctor_id' => null]);
                    }

                    if (!empty($specialtyIds)) {
                        DB::connection('tenant')->table('forms')
                            ->whereIn('specialty_id', $specialtyIds)
                            ->update(['specialty_id' => null]);
                    }
                }

                if (!empty($doctorIds)) {
                    if (Schema::connection('tenant')->hasTable('appointment_waitlist_entries')) {
                        DB::connection('tenant')->table('appointment_waitlist_entries')
                            ->whereIn('doctor_id', $doctorIds)
                            ->delete();
                    }

                    $result['deleted']['doctors'] = Doctor::query()
                        ->whereIn('id', $doctorIds)
                        ->delete();
                }

                if (!empty($userIds)) {
                    $result['deleted']['users'] = User::query()
                        ->whereIn('id', $userIds)
                        ->delete();
                }

                if (!empty($specialtyIds)) {
                    $deletableSpecialtyIds = $this->resolveDeletableSpecialtyIds($specialtyIds);

                    if (!empty($deletableSpecialtyIds)) {
                        $result['deleted']['specialties'] = MedicalSpecialty::query()
                            ->whereIn('id', $deletableSpecialtyIds)
                            ->delete();
                    }
                }
            });

            $this->clearDashboardCache($tenant);
        } catch (Throwable $exception) {
            $result['error'] = $exception->getMessage();
        } finally {
            SpatieTenant::forgetCurrent();
        }

        return $result;
    }

    private function queryDuskPatients()
    {
        $query = Patient::query();

        return $query->where(function ($filter): void {
            if (Schema::connection('tenant')->hasColumn('patients', 'is_test')) {
                $filter->orWhere('is_test', true);
            }

            if (Schema::connection('tenant')->hasColumn('patients', 'test_tag')) {
                $filter->orWhere('test_tag', 'like', 'dusk_%');
            }

            $filter->orWhere('full_name', 'like', 'Paciente Dusk %')
                ->orWhere('full_name', 'like', 'Paciente Edit Base %')
                ->orWhere('full_name', 'like', 'Paciente Agenda Base %')
                ->orWhere('full_name', 'like', 'Paciente Editado Dusk %');
        });
    }

    private function queryDuskAppointments(array $doctorIds, array $patientIds, array $specialtyIds)
    {
        $query = Appointment::query();

        return $query->where(function ($filter) use ($doctorIds, $patientIds, $specialtyIds): void {
            if (Schema::connection('tenant')->hasColumn('appointments', 'is_test')) {
                $filter->orWhere('is_test', true);
            }

            if (Schema::connection('tenant')->hasColumn('appointments', 'test_tag')) {
                $filter->orWhere('test_tag', 'like', 'dusk_%');
            }

            if (Schema::connection('tenant')->hasColumn('appointments', 'notes')) {
                $filter->orWhere('notes', 'like', 'Dusk %')
                    ->orWhere('notes', 'like', 'Agendamento Dusk %');
            }

            if (!empty($doctorIds)) {
                $filter->orWhereIn('doctor_id', $doctorIds);
            }

            if (!empty($patientIds)) {
                $filter->orWhereIn('patient_id', $patientIds);
            }

            if (!empty($specialtyIds)) {
                $filter->orWhereIn('specialty_id', $specialtyIds);
            }
        });
    }

    /**
     * @param  array<int, string>  $specialtyIds
     * @return array<int, string>
     */
    private function resolveDeletableSpecialtyIds(array $specialtyIds): array
    {
        $inUse = collect();

        if (Schema::connection('tenant')->hasTable('doctor_specialty')) {
            $inUse = $inUse->merge(
                DB::connection('tenant')->table('doctor_specialty')
                    ->whereIn('specialty_id', $specialtyIds)
                    ->pluck('specialty_id')
            );
        }

        if (Schema::connection('tenant')->hasTable('forms')) {
            $inUse = $inUse->merge(
                DB::connection('tenant')->table('forms')
                    ->whereIn('specialty_id', $specialtyIds)
                    ->pluck('specialty_id')
            );
        }

        if (Schema::connection('tenant')->hasTable('appointments')) {
            $inUse = $inUse->merge(
                DB::connection('tenant')->table('appointments')
                    ->whereIn('specialty_id', $specialtyIds)
                    ->pluck('specialty_id')
            );
        }

        $blockedIds = $inUse
            ->filter(fn ($id) => !is_null($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        return array_values(array_diff($specialtyIds, $blockedIds));
    }

    private function clearDashboardCache(PlatformTenant $tenant): void
    {
        $tenant->makeCurrent();

        Cache::forget('general_stats_' . $tenant->id);
        Cache::forget('general_stats_' . tenant('id'));

        $now = CarbonImmutable::now();
        $periods = [
            ['type' => 'today', 'start' => $now->startOfDay(), 'end' => $now->endOfDay()],
            ['type' => 'week', 'start' => $now->startOfWeek(), 'end' => $now->endOfWeek()],
            ['type' => 'month', 'start' => $now->startOfMonth(), 'end' => $now->endOfMonth()],
            ['type' => 'year', 'start' => $now->startOfYear(), 'end' => $now->endOfYear()],
        ];

        foreach ($periods as $period) {
            $periodKey = $period['type'] . '_' . $period['start']->format('Y-m-d') . '_' . $period['end']->format('Y-m-d');
            Cache::forget('dashboard_' . $tenant->id . '_' . $periodKey);
            Cache::forget('dashboard_' . tenant('id') . '_' . $periodKey);
        }
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
}
