<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
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
        $dryRun = (bool) $this->option('dry-run');
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

            try {
                $tenant->makeCurrent();
                if (!Schema::connection('tenant')->hasTable('appointments')) {
                    throw new \RuntimeException('Tabela appointments nao encontrada no banco tenant.');
                }

                $columns = Schema::connection('tenant')->getColumnListing('appointments');
                $query = $this->buildTestQuery($columns, $tag);
                $count = (clone $query)->count();
                $filter = $this->describeFilter($columns, $tag);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $plans[$tenant->id] = [
                'count' => $count,
                'filter' => $filter,
                'error' => $error,
            ];

            $previewRows[] = [
                $tenant->subdomain ?: $tenant->id,
                (string) $count,
                $error ? 'erro' : 'ok',
                $error ?: implode(', ', $filter),
            ];
        }

        $this->newLine();
        $this->table(['Tenant', 'Alvo', 'Status', 'Filtro/Erro'], $previewRows);

        if ($dryRun) {
            $this->info('Dry-run: nenhuma exclusao foi executada.');
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
                $appointmentIds = (clone $query)->pluck('id')->all();

                if (!empty($appointmentIds) && $cascade) {
                    $this->purgeRelatedBeforeAppointments($appointmentIds);
                }

                $deleted = $query->delete();
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
    private function buildTestQuery(array $columns, string $tag)
    {
        $query = Appointment::query();

        $hasIsTest = in_array('is_test', $columns, true);
        $hasTestTag = in_array('test_tag', $columns, true);

        if ($hasIsTest && $hasTestTag) {
            return $query->where('is_test', true)->where('test_tag', $tag);
        }

        if ($hasIsTest) {
            return $query->where('is_test', true);
        }

        if ($hasTestTag) {
            return $query->where('test_tag', $tag);
        }

        if (in_array('metadata', $columns, true)) {
            return $query->where('metadata', 'like', '%"is_test":true%')
                ->where('metadata', 'like', '%"test_tag":"' . $tag . '"%');
        }

        if (in_array('notes', $columns, true)) {
            return $query->where('notes', 'like', '%[test_appointment:' . $tag . ']%');
        }

        throw new \RuntimeException(
            'Nenhuma coluna de marcacao de teste encontrada (is_test/test_tag/metadata/notes).'
        );
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function describeFilter(array $columns, string $tag): array
    {
        if (in_array('is_test', $columns, true) && in_array('test_tag', $columns, true)) {
            return ['is_test=1', "test_tag={$tag}"];
        }

        if (in_array('is_test', $columns, true)) {
            return ['is_test=1'];
        }

        if (in_array('test_tag', $columns, true)) {
            return ["test_tag={$tag}"];
        }

        if (in_array('metadata', $columns, true)) {
            return ['metadata contains is_test/test_tag'];
        }

        if (in_array('notes', $columns, true)) {
            return ["notes like [test_appointment:{$tag}]"];
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
