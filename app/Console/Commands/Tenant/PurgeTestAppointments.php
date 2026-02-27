<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class PurgeTestAppointments extends Command
{
    protected $signature = 'tenant:appointments:purge-test
                            {--tenant= : Slug (subdomain) de um tenant especifico}
                            {--all-tenants : Executa em todos os tenants}
                            {--tag=test : Tag para filtro quando existir test_tag}
                            {--confirm : Confirma sem prompt interativo}
                            {--force : Permite execucao em producao}
                            {--cascade : Remove relacionamentos antes de apagar appointments}
                            {--dry-run : Apenas simula a remocao}';

    protected $description = 'Apaga agendamentos de teste (is_test/test_tag) nos tenants selecionados.';

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Execucao bloqueada em producao. Use --force para confirmar.');
            return self::FAILURE;
        }

        $tag = trim((string) $this->option('tag')) ?: 'test';
        // Sem --confirm, o comando opera em modo dry-run por seguranca.
        $dryRun = (bool) $this->option('dry-run') || !(bool) $this->option('confirm');
        $cascade = (bool) $this->option('cascade');

        $tenants = $this->resolveTenants((string) $this->option('tenant'), (bool) $this->option('all-tenants'));
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $previewRows = [];
        $plans = [];

        foreach ($tenants as $tenant) {
            $count = 0;
            $filter = [];
            $error = null;
            $connectionName = '-';
            $databaseName = '-';
            $diagnostics = [
                'total' => 0,
                'is_test' => null,
                'tag_exact' => null,
                'tag_lower' => null,
                'notes_marker' => null,
            ];

            try {
                $tenant->makeCurrent();
                if (!Schema::connection('tenant')->hasTable('appointments')) {
                    throw new \RuntimeException('Tabela appointments nao encontrada no banco tenant.');
                }

                $connectionName = DB::connection('tenant')->getName();
                $databaseName = $this->resolveCurrentDatabaseName();

                $columns = Schema::connection('tenant')->getColumnListing('appointments');
                $query = $this->buildTestQuery($columns, $tag);
                $count = (clone $query)->count();
                $filter = $this->describeFilter($columns, $tag);
                $diagnostics = $this->collectDiagnostics($columns, $tag);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $plans[$tenant->id] = [
                'count' => $count,
                'filter' => $filter,
                'error' => $error,
                'connection' => $connectionName,
                'database' => $databaseName,
                'diagnostics' => $diagnostics,
            ];

            $previewRows[] = [
                $tenant->subdomain ?: $tenant->id,
                $connectionName,
                $databaseName,
                (string) $diagnostics['total'],
                (string) $count,
                $error ? 'erro' : 'ok',
                $error ?: implode(', ', $filter),
            ];

            $this->line(sprintf(
                '[%s] diag total=%d is_test=%s tag_exact=%s tag_lower=%s notes_marker=%s',
                $tenant->subdomain ?: $tenant->id,
                (int) $diagnostics['total'],
                $this->formatNullableInt($diagnostics['is_test']),
                $this->formatNullableInt($diagnostics['tag_exact']),
                $this->formatNullableInt($diagnostics['tag_lower']),
                $this->formatNullableInt($diagnostics['notes_marker'])
            ));
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Conexao', 'Database', 'Appointments', 'Alvo', 'Status', 'Filtro/Erro'],
            $previewRows
        );

        if ($dryRun) {
            $this->info('Dry-run: nenhuma exclusao foi executada (modo padrao sem --confirm).');
            return self::SUCCESS;
        }

        if (!$this->confirmExecution()) {
            return self::SUCCESS;
        }

        $globalStart = microtime(true);
        $summaryRows = [];

        foreach ($tenants as $tenant) {
            $tenantStart = microtime(true);
            $deleted = 0;
            $skipped = 0;
            $errors = 0;
            $message = '-';
            $plan = $plans[$tenant->id] ?? null;

            if (!$plan || $plan['error']) {
                $errors = 1;
                $message = $plan['error'] ?? 'Falha no planejamento.';
                $summaryRows[] = [
                    $tenant->subdomain ?: $tenant->id,
                    (string) $deleted,
                    (string) $skipped,
                    (string) $errors,
                    number_format(microtime(true) - $tenantStart, 2) . 's',
                    $message,
                ];
                continue;
            }

            if (($plan['count'] ?? 0) === 0) {
                $summaryRows[] = [
                    $tenant->subdomain ?: $tenant->id,
                    '0',
                    '0',
                    '0',
                    number_format(microtime(true) - $tenantStart, 2) . 's',
                    'Sem agendamentos de teste para apagar',
                ];
                continue;
            }

            try {
                $tenant->makeCurrent();
                $columns = Schema::connection('tenant')->getColumnListing('appointments');
                $query = $this->buildTestQuery($columns, $tag);
                $appointmentIds = (clone $query)->orderBy('starts_at')->pluck('id')->all();

                $this->printDeletionPreview($appointmentIds);

                if (!empty($appointmentIds) && $cascade) {
                    $this->purgeRelatedBeforeAppointments($appointmentIds);
                }

                $deleted = $this->deleteInChunks($appointmentIds);
                $message = $cascade ? 'Apagado com cascade' : 'Apagado sem cascade';
            } catch (QueryException $exception) {
                $errors = 1;
                $skipped = (int) ($plan['count'] ?? 0);
                $message = 'Bloqueado por FK. Tente novamente com --cascade.';
                $this->error("[{$tenant->subdomain}] {$message}");
                report($exception);
            } catch (Throwable $exception) {
                $errors = 1;
                $message = $exception->getMessage();
                $this->error("[{$tenant->subdomain}] falha: {$message}");
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $summaryRows[] = [
                $tenant->subdomain ?: $tenant->id,
                (string) $deleted,
                (string) $skipped,
                (string) $errors,
                number_format(microtime(true) - $tenantStart, 2) . 's',
                $message,
            ];
        }

        $this->newLine();
        $this->table(['Tenant', 'Apagados', 'Ignorados', 'Erros', 'Tempo', 'Detalhes'], $summaryRows);
        $this->info('Tempo total: ' . number_format(microtime(true) - $globalStart, 2) . 's');

        return self::SUCCESS;
    }

    private function confirmExecution(): bool
    {
        if ((bool) $this->option('confirm')) {
            return true;
        }

        if (!$this->input->isInteractive()) {
            $this->error('Use --confirm em modo nao interativo.');
            return false;
        }

        if (!$this->confirm('Confirmar remocao dos agendamentos de teste listados?', false)) {
            $this->warn('Operacao cancelada.');
            return false;
        }

        return true;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function buildTestQuery(array $columns, string $tag): EloquentBuilder
    {
        $query = Appointment::query();

        $hasIsTest = in_array('is_test', $columns, true);
        $hasTestTag = in_array('test_tag', $columns, true);
        $hasNotes = in_array('notes', $columns, true);
        $hasMetadata = in_array('metadata', $columns, true);
        $normalizedTag = mb_strtolower($tag);
        $hasAnyFilter = $hasIsTest || $hasTestTag || $hasNotes || $hasMetadata;

        if (!$hasAnyFilter) {
            throw new \RuntimeException(
                'Nenhuma coluna de marcacao de teste encontrada (is_test/test_tag/metadata/notes).'
            );
        }

        return $query->where(function (EloquentBuilder $filter) use (
            $hasIsTest,
            $hasTestTag,
            $hasNotes,
            $hasMetadata,
            $normalizedTag
        ): void {
            if ($hasIsTest) {
                $filter->orWhere('is_test', true);
            }

            if ($hasTestTag) {
                $filter->orWhereRaw('lower(test_tag) = ?', [$normalizedTag]);
            }

            if ($hasMetadata) {
                $filter->orWhere(function (EloquentBuilder $metadataFilter) use ($normalizedTag): void {
                    $metadataFilter->whereRaw('lower(metadata) like ?', ['%"is_test":true%'])
                        ->whereRaw('lower(metadata) like ?', ['%"test_tag":"' . $normalizedTag . '"%']);
                });
            }

            if ($hasNotes) {
                // Marcador legado usado pelos seeders quando is_test/test_tag nao estavam disponiveis.
                $filter->orWhereRaw('lower(notes) like ?', ['%[test_appointment:' . $normalizedTag . ']%'])
                    ->orWhereRaw('lower(notes) like ?', ['%[test_appointment:%']);
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function describeFilter(array $columns, string $tag): array
    {
        $normalizedTag = mb_strtolower($tag);
        $parts = [];

        if (in_array('is_test', $columns, true)) {
            $parts[] = 'is_test=1';
        }

        if (in_array('test_tag', $columns, true)) {
            $parts[] = 'lower(test_tag)=' . $normalizedTag;
        }

        if (in_array('metadata', $columns, true)) {
            $parts[] = 'metadata is_test/test_tag';
        }

        if (in_array('notes', $columns, true)) {
            $parts[] = 'notes like [test_appointment:*]';
        }

        if (!empty($parts)) {
            return ['OR(' . implode(' | ', $parts) . ')'];
        }

        return ['sem filtro valido'];
    }

    /**
     * @param  array<int, string>  $appointmentIds
     */
    private function purgeRelatedBeforeAppointments(array $appointmentIds): void
    {
        foreach (array_chunk($appointmentIds, 200) as $chunk) {
            DB::connection('tenant')->transaction(function () use ($chunk): void {
                if (Schema::connection('tenant')->hasTable('online_appointment_instructions')) {
                    DB::connection('tenant')->table('online_appointment_instructions')
                        ->whereIn('appointment_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('calendar_sync_state')) {
                    DB::connection('tenant')->table('calendar_sync_state')
                        ->whereIn('appointment_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('form_responses')) {
                    DB::connection('tenant')->table('form_responses')
                        ->whereIn('appointment_id', $chunk)
                        ->delete();
                }
            });
        }
    }

    /**
     * @param  array<int, string>  $columns
     * @return array{total:int,is_test:int|null,tag_exact:int|null,tag_lower:int|null,notes_marker:int|null}
     */
    private function collectDiagnostics(array $columns, string $tag): array
    {
        $normalizedTag = mb_strtolower($tag);
        $diagnostics = [
            'total' => Appointment::query()->count(),
            'is_test' => null,
            'tag_exact' => null,
            'tag_lower' => null,
            'notes_marker' => null,
        ];

        if (in_array('is_test', $columns, true)) {
            $diagnostics['is_test'] = Appointment::query()->where('is_test', true)->count();
        }

        if (in_array('test_tag', $columns, true)) {
            $diagnostics['tag_exact'] = Appointment::query()->where('test_tag', $tag)->count();
            $diagnostics['tag_lower'] = Appointment::query()
                ->whereRaw('lower(test_tag) = ?', [$normalizedTag])
                ->count();
        }

        if (in_array('notes', $columns, true)) {
            $diagnostics['notes_marker'] = Appointment::query()
                ->whereRaw('lower(notes) like ?', ['%[test_appointment:%'])
                ->count();
        }

        return $diagnostics;
    }

    private function resolveCurrentDatabaseName(): string
    {
        $driver = (string) config('database.connections.tenant.driver');

        return match ($driver) {
            'pgsql' => (string) DB::connection('tenant')->scalar('select current_database()'),
            'mysql' => (string) DB::connection('tenant')->scalar('select database()'),
            default => (string) config('database.connections.tenant.database', '-'),
        };
    }

    private function formatNullableInt(?int $value): string
    {
        return $value === null ? '-' : (string) $value;
    }

    /**
     * @param  array<int, string>  $appointmentIds
     */
    private function printDeletionPreview(array $appointmentIds): void
    {
        if (empty($appointmentIds)) {
            return;
        }

        $preview = Appointment::query()
            ->with(['patient:id,full_name', 'doctor.user:id,name,name_full'])
            ->whereIn('id', array_slice($appointmentIds, 0, 10))
            ->orderBy('starts_at')
            ->get();

        $rows = $preview->map(function (Appointment $appointment): array {
            return [
                (string) $appointment->id,
                (string) optional($appointment->starts_at)->format('Y-m-d H:i'),
                (string) optional($appointment->doctor?->user)->name_full
                    ?: (string) optional($appointment->doctor?->user)->name
                    ?: '-',
                (string) optional($appointment->patient)->full_name ?: '-',
            ];
        })->all();

        $this->line('Preview dos IDs que serao removidos (ate 10 registros):');
        $this->table(['ID', 'Inicio', 'Medico', 'Paciente'], $rows);
        if (count($appointmentIds) > 10) {
            $this->line('... ' . (count($appointmentIds) - 10) . ' registro(s) adicional(is).');
        }
    }

    /**
     * @param  array<int, string>  $appointmentIds
     */
    private function deleteInChunks(array $appointmentIds): int
    {
        $deleted = 0;

        foreach (array_chunk($appointmentIds, 200) as $chunk) {
            DB::connection('tenant')->transaction(function () use (&$deleted, $chunk): void {
                $deleted += Appointment::query()->whereIn('id', $chunk)->delete();
            });
        }

        return $deleted;
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
