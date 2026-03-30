<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\MedicalSpecialtyCatalog;
use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Module;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class SeedFakeDoctors extends Command
{
    protected $signature = 'tenant:doctors:seed
                            {--tenant= : Slug (subdomain) ou UUID de um tenant especifico}
                            {--all-tenants : Executa em todos os tenants}
                            {--count=10 : Quantidade de medicos por tenant}
                            {--password=Teste@123 : Senha para login dos usuarios medicos}
                            {--tag=fake_doctor : Marcador para e-mail e identificacao}
                            {--dry-run : Simula sem gravar}
                            {--force : Permite execucao em producao}';

    protected $description = 'Cria medicos ficticios completos e agendaveis (user + doctor + especialidade + agenda) por tenant.';

    /**
     * @var array<int, string>
     */
    private array $maleFirstNames = [
        'Joao', 'Jose', 'Antonio', 'Francisco', 'Carlos', 'Paulo', 'Pedro', 'Lucas', 'Luiz', 'Marcos',
        'Gabriel', 'Rafael', 'Daniel', 'Eduardo', 'Bruno', 'Diego', 'Felipe', 'Rodrigo', 'Andre', 'Ricardo',
        'Gustavo', 'Leonardo', 'Tiago', 'Matheus', 'Vinicius', 'Henrique', 'Caio', 'Murilo', 'Vitor', 'Samuel',
        'Igor', 'Fernando', 'Leandro', 'Marcelo', 'Julio', 'Alexandre', 'Otavio', 'Renato', 'Fabio', 'Wagner',
    ];

    /**
     * @var array<int, string>
     */
    private array $femaleFirstNames = [
        'Maria', 'Ana', 'Francisca', 'Antonia', 'Adriana', 'Juliana', 'Marcia', 'Fernanda', 'Patricia', 'Aline',
        'Camila', 'Amanda', 'Bruna', 'Carla', 'Daniela', 'Eduarda', 'Gabriela', 'Helena', 'Isabela', 'Larissa',
        'Leticia', 'Luana', 'Manuela', 'Mariana', 'Natasha', 'Priscila', 'Renata', 'Sabrina', 'Tatiana', 'Vanessa',
        'Yasmin', 'Bianca', 'Beatriz', 'Cecilia', 'Debora', 'Elisa', 'Fabiana', 'Giovana', 'Ingrid', 'Paula',
    ];

    /**
     * @var array<int, string>
     */
    private array $surnames = [
        'Albuquerque', 'Amaral', 'Andrade', 'Barros', 'Bittencourt', 'Braga', 'Carvalho', 'Cavalcanti', 'Coelho', 'Correia',
        'Dias', 'Domingues', 'Esteves', 'Garcia', 'Leite', 'Maia', 'Marques', 'Martins', 'Medeiros', 'Moura',
        'Neves', 'Pinto', 'Queiroz', 'Ribeiro', 'Rocha', 'Sales', 'Santana', 'Santos', 'Silva', 'Souza',
        'Teixeira', 'Valente', 'Vargas', 'Vieira', 'Xavier', 'Assis', 'Borges', 'Chaves', 'Delgado', 'Tavares',
        'Alves', 'Araujo', 'Barbosa', 'Batista', 'Campos', 'Cardoso', 'Castro', 'Costa', 'Cruz', 'Duarte',
        'Farias', 'Fernandes', 'Ferreira', 'Freitas', 'Gomes', 'Lima', 'Lopes', 'Machado', 'Melo', 'Mendes',
        'Monteiro', 'Moraes', 'Moreira', 'Nogueira', 'Oliveira', 'Pacheco', 'Pereira', 'Ramos', 'Reis', 'Rezende',
    ];

    /**
     * @var array<int, string>
     */
    private array $crmStates = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
        'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    /**
     * @var array<int, array{weekday:int,start:string,end:string}>
     */
    private array $defaultBusinessHours = [
        ['weekday' => 1, 'start' => '08:00:00', 'end' => '12:00:00'],
        ['weekday' => 1, 'start' => '13:00:00', 'end' => '18:00:00'],
        ['weekday' => 2, 'start' => '08:00:00', 'end' => '12:00:00'],
        ['weekday' => 2, 'start' => '13:00:00', 'end' => '18:00:00'],
        ['weekday' => 3, 'start' => '08:00:00', 'end' => '12:00:00'],
        ['weekday' => 3, 'start' => '13:00:00', 'end' => '18:00:00'],
        ['weekday' => 4, 'start' => '08:00:00', 'end' => '12:00:00'],
        ['weekday' => 4, 'start' => '13:00:00', 'end' => '18:00:00'],
        ['weekday' => 5, 'start' => '08:00:00', 'end' => '12:00:00'],
        ['weekday' => 5, 'start' => '13:00:00', 'end' => '18:00:00'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private array $columnCache = [];

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

        $password = trim((string) $this->option('password'));
        if ($password === '') {
            $this->error('Informe uma senha valida em --password.');
            return self::FAILURE;
        }

        $tag = Str::slug((string) $this->option('tag'));
        if ($tag === '') {
            $tag = 'fake-doctor';
        }

        $dryRun = (bool) $this->option('dry-run');
        $tenants = $this->resolveTenants((string) $this->option('tenant'), (bool) $this->option('all-tenants'));
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $rows = [];
        $globalStart = microtime(true);

        foreach ($tenants as $tenant) {
            $tenantStart = microtime(true);
            $usedNames = [];
            $stats = [
                'requested' => $count,
                'users_created' => 0,
                'users_updated' => 0,
                'doctors_created' => 0,
                'doctors_updated' => 0,
                'calendars_created' => 0,
                'appointment_types_created' => 0,
                'business_hours_created' => 0,
                'errors' => 0,
            ];
            $details = '-';

            try {
                $tenant->makeCurrent();
                $this->columnCache = [];
                $this->assertRequiredTables();
                $specialties = $this->ensureSpecialtiesAvailable();

                if ($specialties->isEmpty()) {
                    throw new \RuntimeException('Nenhuma especialidade disponivel no tenant.');
                }

                for ($index = 1; $index <= $count; $index++) {
                    $profile = $this->buildDoctorProfile($tenant, $index, $tag, $specialties, $usedNames);

                    if ($dryRun) {
                        $this->simulateDoctorUpsert($profile, $stats);
                        continue;
                    }

                    $result = $this->upsertDoctor($tenant, $profile, $password);
                    foreach ($result as $key => $value) {
                        $stats[$key] = ($stats[$key] ?? 0) + $value;
                    }
                }
            } catch (Throwable $exception) {
                $stats['errors']++;
                $details = $exception->getMessage();
                $this->error(sprintf('[%s] falha: %s', $tenant->subdomain ?: $tenant->id, $details));
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $rows[] = [
                (string) ($tenant->subdomain ?: $tenant->id),
                (string) $stats['requested'],
                (string) $stats['users_created'],
                (string) $stats['users_updated'],
                (string) $stats['doctors_created'],
                (string) $stats['doctors_updated'],
                (string) $stats['calendars_created'],
                (string) $stats['business_hours_created'],
                (string) $stats['appointment_types_created'],
                (string) $stats['errors'],
                number_format(microtime(true) - $tenantStart, 2) . 's',
                $details,
            ];
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Requested', 'UsrNew', 'UsrUpd', 'DocNew', 'DocUpd', 'CalNew', 'BHourNew', 'TypeNew', 'Errors', 'Time', 'Details'],
            $rows
        );

        $this->info('Tempo total: ' . number_format(microtime(true) - $globalStart, 2) . 's');

        if ($dryRun) {
            $this->warn('Dry-run: nenhuma gravacao foi executada.');
        } else {
            $this->info('Credencial padrao configurada para novos/atualizados: ' . $password);
        }

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, PlatformTenant>
     */
    private function resolveTenants(string $tenantSlug, bool $allTenants): Collection
    {
        if ($tenantSlug !== '' && $allTenants) {
            $this->error('Use apenas uma opcao: --tenant ou --all-tenants.');
            return collect();
        }

        if ($tenantSlug === '' && !$allTenants) {
            $this->error('Informe --tenant=<slug|uuid> ou --all-tenants.');
            return collect();
        }

        if ($allTenants) {
            $tenants = PlatformTenant::query()->orderBy('subdomain')->get();
            if ($tenants->isEmpty()) {
                $this->warn('Nenhum tenant encontrado.');
            }
            return $tenants;
        }

        $query = PlatformTenant::query()->where('subdomain', $tenantSlug);
        if (Str::isUuid($tenantSlug)) {
            $query->orWhere('id', $tenantSlug);
        }

        $tenant = $query->first();
        if (!$tenant) {
            $this->error("Tenant nao encontrado: {$tenantSlug}");
            return collect();
        }

        return collect([$tenant]);
    }

    /**
     * @return Collection<int, MedicalSpecialty>
     */
    private function ensureSpecialtiesAvailable(): Collection
    {
        $existing = MedicalSpecialty::query()->orderBy('name')->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        try {
            $catalogRows = MedicalSpecialtyCatalog::on('pgsql')
                ->where('type', 'medical_specialty')
                ->orderBy('name')
                ->limit(80)
                ->get(['id', 'name', 'code']);
        } catch (Throwable) {
            $catalogRows = collect();
        }

        foreach ($catalogRows as $catalog) {
            MedicalSpecialty::query()->firstOrCreate(
                ['name' => (string) $catalog->name],
                [
                    'id' => (string) ($catalog->id ?: Str::uuid()),
                    'code' => $catalog->code,
                    'label_singular' => 'Especialidade',
                    'label_plural' => 'Especialidades',
                    'registration_label' => 'Registro',
                ]
            );
        }

        if ($catalogRows->isEmpty()) {
            $fallbackNames = [
                'Clinica Medica',
                'Cardiologia',
                'Dermatologia',
                'Pediatria',
                'Ginecologia',
                'Ortopedia',
                'Neurologia',
                'Psiquiatria',
            ];

            foreach ($fallbackNames as $name) {
                MedicalSpecialty::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'id' => (string) Str::uuid(),
                        'code' => null,
                        'label_singular' => 'Especialidade',
                        'label_plural' => 'Especialidades',
                        'registration_label' => 'Registro',
                    ]
                );
            }
        }

        return MedicalSpecialty::query()->orderBy('name')->get();
    }

    /**
     * @param  Collection<int, MedicalSpecialty>  $specialties
     * @param  array<string, bool>  $usedNames
     * @return array<string, mixed>
     */
    private function buildDoctorProfile(
        PlatformTenant $tenant,
        int $index,
        string $tag,
        Collection $specialties,
        array &$usedNames
    ): array
    {
        $baseSeed = (string) $tenant->id . '|' . $index;
        $tenantKey = Str::of((string) ($tenant->subdomain ?: $tenant->id))
            ->lower()
            ->replaceMatches('/[^a-z0-9]/', '')
            ->substr(0, 16)
            ->value();
        $tenantKey = $tenantKey !== '' ? $tenantKey : 'tenant';

        $tagKey = Str::of($tag)
            ->lower()
            ->replaceMatches('/[^a-z0-9]/', '')
            ->substr(0, 12)
            ->value();
        $tagKey = $tagKey !== '' ? $tagKey : 'seed';

        $sequence = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        $email = sprintf('medico.%s.%s.%s@tenant.test', $tenantKey, $tagKey, $sequence);

        $phoneDdd = str_pad((string) (11 + ($this->stableHashNumber($baseSeed . '|ddd') % 89)), 2, '0', STR_PAD_LEFT);
        $phoneTail = str_pad((string) ($this->stableHashNumber($baseSeed . '|phone') % 100000000), 8, '0', STR_PAD_LEFT);
        $phone = $phoneDdd . '9' . $phoneTail;

        $fullName = '';
        $shortDisplayName = '';
        $state = '';
        $crmNumber = '';
        $genderPrefix = '';

        for ($attempt = 0; $attempt < 40; $attempt++) {
            $seed = $baseSeed . '|v' . $attempt;
            $isFemale = ($this->stableHashNumber($seed . '|gender') % 2) === 0;
            $firstNameList = $isFemale ? $this->femaleFirstNames : $this->maleFirstNames;
            $genderPrefix = $isFemale ? 'Dra.' : 'Dr.';

            $given1 = $this->pickFromList($firstNameList, $seed . '|given1');
            $useSecondGiven = ($this->stableHashNumber($seed . '|use_second_given') % 100) < 42;
            $given2 = $useSecondGiven ? $this->pickDistinctFromList($firstNameList, $given1, $seed . '|given2') : '';

            $surname1 = $this->pickFromList($this->surnames, $seed . '|surname1');
            $surname2 = $this->pickDistinctFromList($this->surnames, $surname1, $seed . '|surname2');
            $useThirdSurname = ($this->stableHashNumber($seed . '|use_third_surname') % 100) < 18;
            $surname3 = $useThirdSurname
                ? $this->pickDistinctFromList($this->surnames, $surname1 . '|' . $surname2, $seed . '|surname3')
                : '';

            $fullName = trim(implode(' ', array_values(array_filter([$given1, $given2, $surname1, $surname2, $surname3]))));
            $shortCore = $this->buildShortDisplayName($fullName);
            $shortDisplayName = trim($genderPrefix . ' ' . $shortCore);
            $nameKey = Str::lower($fullName . '|' . $shortDisplayName);

            if (!isset($usedNames[$nameKey])) {
                $usedNames[$nameKey] = true;
                break;
            }
        }

        if ($fullName === '') {
            $fullName = 'Joao Silva Santos';
            $shortDisplayName = 'Dr. Joao Silva';
            $genderPrefix = 'Dr.';
        }

        $state = $this->pickFromList($this->crmStates, $baseSeed . '|uf');
        $crmNumber = $this->generateCrmNumber($baseSeed);
        $registrationValue = 'CRM/' . $state . ' ' . $crmNumber;

        /** @var MedicalSpecialty $specialty */
        $specialty = $specialties->values()->get($this->stableHashNumber($baseSeed . '|spec') % $specialties->count());

        return [
            'seed' => $baseSeed,
            'email' => $email,
            'name' => $shortDisplayName,
            'name_full' => $fullName,
            'phone' => $phone,
            'crm_state' => $state,
            'crm_number' => $crmNumber,
            'registration_value' => $registrationValue,
            'specialty_id' => (string) $specialty->id,
            'specialty_name' => (string) $specialty->name,
            'calendar_name' => 'Agenda ' . $shortDisplayName,
            'calendar_external_id' => sprintf('seed:%s:%s', $tag, substr(md5($baseSeed), 0, 12)),
            'appointment_type_name' => 'Consulta Inicial',
            'appointment_duration' => 30,
            'modules' => $this->resolveDoctorModules(),
            'signature' => sprintf('Atendimento humanizado em %s, com foco em prevencao e acompanhamento continuo.', $specialty->name),
            'doctor_display_prefix' => $genderPrefix,
        ];
    }

    /**
     * @param array<string, mixed> $profile
     * @param array<string, int> $stats
     */
    private function simulateDoctorUpsert(array $profile, array &$stats): void
    {
        $user = User::query()->where('email', (string) $profile['email'])->first();
        if ($user) {
            if ((string) $user->role !== 'doctor') {
                $stats['errors']++;
                return;
            }
            $stats['users_updated']++;
            $doctor = Doctor::query()->where('user_id', $user->id)->first();
            if ($doctor) {
                $stats['doctors_updated']++;
            } else {
                $stats['doctors_created']++;
                $stats['calendars_created']++;
                $stats['appointment_types_created']++;
                $stats['business_hours_created'] += count($this->defaultBusinessHours);
            }
            return;
        }

        $stats['users_created']++;
        $stats['doctors_created']++;
        $stats['calendars_created']++;
        $stats['appointment_types_created']++;
        $stats['business_hours_created'] += count($this->defaultBusinessHours);
    }

    /**
     * @param array<string, mixed> $profile
     * @return array<string, int>
     */
    private function upsertDoctor(PlatformTenant $tenant, array $profile, string $password): array
    {
        return DB::connection('tenant')->transaction(function () use ($tenant, $profile, $password): array {
            $stats = [
                'users_created' => 0,
                'users_updated' => 0,
                'doctors_created' => 0,
                'doctors_updated' => 0,
                'calendars_created' => 0,
                'appointment_types_created' => 0,
                'business_hours_created' => 0,
                'errors' => 0,
            ];

            $email = (string) $profile['email'];
            $user = User::query()->where('email', $email)->first();
            $userCreatePayload = [
                'name' => (string) $profile['name'],
                'name_full' => (string) $profile['name_full'],
                'telefone' => (string) $profile['phone'],
                'email' => $email,
                'password' => $password,
                'is_doctor' => true,
                'status' => 'active',
                'role' => 'doctor',
            ];
            if ($this->hasTenantColumn('users', 'tenant_id')) {
                $userCreatePayload['tenant_id'] = (string) $tenant->id;
            }
            if ($this->hasTenantColumn('users', 'is_system')) {
                $userCreatePayload['is_system'] = false;
            }
            if ($this->hasTenantColumn('users', 'modules')) {
                $userCreatePayload['modules'] = $profile['modules'];
            }

            if (!$user) {
                $user = User::query()->create($userCreatePayload);
                $stats['users_created']++;
            } else {
                if ((string) $user->role !== 'doctor') {
                    throw new \RuntimeException("Conflito: e-mail {$email} ja existe para role {$user->role}.");
                }

                $userUpdatePayload = $userCreatePayload;
                unset($userUpdatePayload['email']);
                $user->fill($userUpdatePayload);

                // So atualiza modules se vazio, para nao sobrescrever personalizacao existente.
                if ($this->hasTenantColumn('users', 'modules') && empty($user->modules)) {
                    $user->modules = $profile['modules'];
                }

                $user->save();
                $stats['users_updated']++;
            }

            $doctor = Doctor::query()->where('user_id', $user->id)->first();

            $crmNumber = (string) $profile['crm_number'];
            $crmState = (string) $profile['crm_state'];

            if ($doctor) {
                $conflict = Doctor::query()
                    ->where('id', '!=', $doctor->id)
                    ->where('crm_number', $crmNumber)
                    ->where('crm_state', $crmState)
                    ->exists();
            } else {
                $conflict = Doctor::query()
                    ->where('crm_number', $crmNumber)
                    ->where('crm_state', $crmState)
                    ->exists();
            }

            if ($conflict) {
                $crmNumber = $this->generateNonConflictingCrmNumber($crmNumber, $crmState);
            }
            $registrationValue = 'CRM/' . $crmState . ' ' . $crmNumber;

            if (!$doctor) {
                $doctor = Doctor::query()->create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'crm_number' => $crmNumber,
                    'crm_state' => $crmState,
                    'signature' => (string) $profile['signature'],
                    'label_singular' => 'Medico',
                    'label_plural' => 'Medicos',
                    'registration_label' => 'CRM',
                    'registration_value' => $registrationValue,
                ]);
                $stats['doctors_created']++;
            } else {
                $doctor->update([
                    'crm_number' => $crmNumber,
                    'crm_state' => $crmState,
                    'signature' => (string) $profile['signature'],
                    'registration_label' => 'CRM',
                    'registration_value' => $registrationValue,
                ]);
                $stats['doctors_updated']++;
            }

            $doctor->specialties()->syncWithoutDetaching([(string) $profile['specialty_id']]);

            $calendar = Calendar::query()->where('doctor_id', $doctor->id)->first();
            if (!$calendar) {
                $calendarPayload = [
                    'id' => (string) Str::uuid(),
                    'doctor_id' => $doctor->id,
                    'name' => (string) $profile['calendar_name'],
                    'external_id' => (string) $profile['calendar_external_id'],
                ];
                if ($this->hasTenantColumn('calendars', 'is_active')) {
                    $calendarPayload['is_active'] = true;
                }
                Calendar::query()->create($calendarPayload);
                $stats['calendars_created']++;
            } else {
                if ($this->hasTenantColumn('calendars', 'is_active')) {
                    $calendar->is_active = true;
                }
                if (trim((string) $calendar->name) === '') {
                    $calendar->name = (string) $profile['calendar_name'];
                }
                if (trim((string) $calendar->external_id) === '') {
                    $calendar->external_id = (string) $profile['calendar_external_id'];
                }
                $calendar->save();
            }

            foreach ($this->defaultBusinessHours as $row) {
                $businessHour = BusinessHour::query()->firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'weekday' => $row['weekday'],
                        'start_time' => $row['start'],
                        'end_time' => $row['end'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'break_start_time' => null,
                        'break_end_time' => null,
                    ]
                );

                if ($businessHour->wasRecentlyCreated) {
                    $stats['business_hours_created']++;
                }
            }

            $appointmentType = AppointmentType::query()
                ->where('doctor_id', $doctor->id)
                ->where('name', (string) $profile['appointment_type_name'])
                ->first();

            if (!$appointmentType) {
                AppointmentType::query()->create([
                    'id' => (string) Str::uuid(),
                    'doctor_id' => $doctor->id,
                    'name' => (string) $profile['appointment_type_name'],
                    'duration_min' => (int) $profile['appointment_duration'],
                    'is_active' => true,
                ]);
                $stats['appointment_types_created']++;
            } else {
                $appointmentType->update([
                    'duration_min' => max(1, (int) $appointmentType->duration_min),
                    'is_active' => true,
                ]);
            }

            return $stats;
        });
    }

    private function generateNonConflictingCrmNumber(string $baseNumber, string $state): string
    {
        $number = max(100000, (int) preg_replace('/\D+/', '', $baseNumber));

        for ($i = 0; $i < 50; $i++) {
            $candidate = (string) ($number + $i + 1);
            $exists = Doctor::query()
                ->where('crm_number', $candidate)
                ->where('crm_state', $state)
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        return (string) ($number + random_int(51, 999));
    }

    private function generateCrmNumber(string $seed): string
    {
        $raw = 100000 + ($this->stableHashNumber($seed . '|crm') % 899999);
        return (string) $raw;
    }

    /**
     * @param array<int, string> $items
     */
    private function pickFromList(array $items, string $seed): string
    {
        if (empty($items)) {
            return '';
        }

        return $items[$this->stableHashNumber($seed) % count($items)];
    }

    /**
     * @param  array<int, string>  $items
     */
    private function pickDistinctFromList(array $items, string $except, string $seed): string
    {
        if (empty($items)) {
            return '';
        }

        $normalizedExcept = array_filter(explode('|', Str::lower($except)));
        $candidate = $this->pickFromList($items, $seed);

        if (!in_array(Str::lower($candidate), $normalizedExcept, true)) {
            return $candidate;
        }

        for ($i = 1; $i <= count($items); $i++) {
            $fallback = $items[($this->stableHashNumber($seed) + $i) % count($items)];
            if (!in_array(Str::lower($fallback), $normalizedExcept, true)) {
                return $fallback;
            }
        }

        return $candidate;
    }

    private function buildShortDisplayName(string $fullName): string
    {
        $tokens = preg_split('/\s+/', trim($fullName)) ?: [];
        $stopWords = ['de', 'da', 'do', 'das', 'dos', 'e'];
        $useful = [];

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            if (in_array(Str::lower($token), $stopWords, true)) {
                continue;
            }

            $useful[] = $token;
            if (count($useful) === 2) {
                break;
            }
        }

        if (count($useful) >= 2) {
            return implode(' ', array_slice($useful, 0, 2));
        }

        return implode(' ', array_slice($tokens, 0, 2));
    }

    private function stableHashNumber(string $seed): int
    {
        return (int) sprintf('%u', crc32($seed));
    }

    private function assertRequiredTables(): void
    {
        $requiredTables = [
            'users',
            'doctors',
            'medical_specialties',
            'doctor_specialty',
            'calendars',
            'business_hours',
            'appointment_types',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::connection('tenant')->hasTable($table)) {
                throw new \RuntimeException("Tabela obrigatoria nao encontrada no tenant: {$table}");
            }
        }
    }

    private function hasTenantColumn(string $table, string $column): bool
    {
        $columns = $this->columnCache[$table] ?? null;
        if ($columns === null) {
            $columns = Schema::connection('tenant')->getColumnListing($table);
            $this->columnCache[$table] = $columns;
        }

        return in_array($column, $columns, true);
    }

    /**
     * @return array<int, string>
     */
    private function resolveDoctorModules(): array
    {
        $setting = TenantSetting::get('user_defaults.modules_doctor', '[]');
        if (is_array($setting)) {
            $values = $setting;
        } elseif (is_string($setting)) {
            $decoded = json_decode($setting, true);
            $values = is_array($decoded) ? $decoded : [];
        } else {
            $values = [];
        }

        $values = array_values(array_unique(array_filter($values, static fn ($value): bool => is_string($value) && $value !== '')));
        if (!empty($values)) {
            return $values;
        }

        $fallback = ['appointments', 'calendar', 'patients', 'doctors', 'specialties'];
        $availableKeys = collect(Module::available())->pluck('key')->all();

        return array_values(array_intersect($fallback, $availableKeys));
    }
}
