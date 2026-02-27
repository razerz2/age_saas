<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Patient;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class PurgeTestPatients extends Command
{
    protected $signature = 'tenant:patients:purge-test
                            {--tenant= : Slug (subdomain) de um tenant especifico}
                            {--all-tenants : Executa em todos os tenants}
                            {--tag=test : Tag de teste para filtro}
                            {--hard : Hard delete (padrao)}
                            {--soft : Soft delete (se suportado pelo model)}
                            {--confirm : Confirma sem prompt interativo}
                            {--cascade : Remove dependencias antes de apagar pacientes}
                            {--force : Permite execucao em producao}';

    protected $description = 'Apaga pacientes de teste (is_test/test_tag) nos tenants selecionados.';

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Execucao bloqueada em producao. Use --force para confirmar.');
            return self::FAILURE;
        }

        $tag = trim((string) $this->option('tag'));
        if ($tag === '') {
            $tag = 'test';
        }

        $tenants = $this->resolveTenants((string) $this->option('tenant'), (bool) $this->option('all-tenants'));
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $useSoftDeleteRequested = (bool) $this->option('soft');
        $cascade = (bool) $this->option('cascade');
        $globalStart = microtime(true);

        $previewRows = [];
        $tenantPlans = [];
        foreach ($tenants as $tenant) {
            $count = 0;
            $filter = [];
            $error = null;

            try {
                $tenant->makeCurrent();

                if (!Schema::connection('tenant')->hasTable('patients')) {
                    throw new \RuntimeException('Tabela patients nao encontrada no banco tenant.');
                }

                $columns = Schema::connection('tenant')->getColumnListing('patients');
                $query = $this->buildTestPatientsQuery($columns, $tag);
                $count = (clone $query)->count();
                $filter = $this->buildFilterDescription($columns, $tag);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $tenantPlans[$tenant->id] = [
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

        $forceConfirm = (bool) $this->option('confirm');
        if (!$forceConfirm) {
            if (!$this->input->isInteractive()) {
                $this->error('Use --confirm em modo nao interativo.');
                return self::FAILURE;
            }

            if (!$this->confirm('Confirmar exclusao de pacientes de teste nos tenants selecionados?', false)) {
                $this->warn('Operacao cancelada.');
                return self::SUCCESS;
            }
        }

        $summaryRows = [];
        foreach ($tenants as $tenant) {
            $tenantStart = microtime(true);
            $deleted = 0;
            $skipped = 0;
            $errors = 0;
            $message = '-';
            $plan = $tenantPlans[$tenant->id] ?? null;

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
                    'Sem pacientes de teste para apagar',
                ];
                continue;
            }

            try {
                $tenant->makeCurrent();

                $columns = Schema::connection('tenant')->getColumnListing('patients');
                $query = $this->buildTestPatientsQuery($columns, $tag);
                $useSoftDelete = $useSoftDeleteRequested;

                if ($useSoftDelete && !$this->patientSupportsSoftDelete($columns)) {
                    $this->warn("[{$tenant->subdomain}] --soft ignorado (model/tabela sem SoftDeletes).");
                    $useSoftDelete = false;
                }

                if ($useSoftDelete) {
                    $deleted = $query->delete();
                    $message = 'Soft delete aplicado.';
                } else {
                    $patientIds = (clone $query)->pluck('id')->all();
                    if (empty($patientIds)) {
                        $message = 'Nenhum registro elegivel no momento da exclusao.';
                    } elseif ($cascade) {
                        $deleted = $this->deleteWithCascade($patientIds);
                        $message = 'Hard delete com cascade.';
                    } else {
                        $deleted = $query->delete();
                        $message = 'Hard delete sem cascade.';
                    }
                }
            } catch (QueryException $exception) {
                $errors = 1;
                $skipped = $plan['count'];
                $message = 'Bloqueado por FK. Use --cascade para remover dependencias.';
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

    private function buildTestPatientsQuery(array $columns, string $tag)
    {
        $query = Patient::query();

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
            return $query->where('notes', 'like', '%[test:' . $tag . ']%');
        }

        if (in_array('tags', $columns, true)) {
            return $query->where('tags', 'like', '%' . $tag . '%');
        }

        throw new \RuntimeException(
            'Nenhuma coluna de marcacao de teste encontrada (is_test/test_tag/metadata/notes/tags).'
        );
    }

    private function buildFilterDescription(array $columns, string $tag): array
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
            return ["notes like [test:{$tag}]"];
        }

        if (in_array('tags', $columns, true)) {
            return ["tags like {$tag}"];
        }

        return ['sem filtro valido'];
    }

    private function patientSupportsSoftDelete(array $columns): bool
    {
        $usesSoftDeletes = in_array(
            'Illuminate\\Database\\Eloquent\\SoftDeletes',
            class_uses_recursive(Patient::class),
            true
        );

        return $usesSoftDeletes && in_array('deleted_at', $columns, true);
    }

    private function deleteWithCascade(array $patientIds): int
    {
        $deleted = 0;

        foreach (array_chunk($patientIds, 200) as $chunk) {
            DB::connection('tenant')->transaction(function () use (&$deleted, $chunk): void {
                if (Schema::connection('tenant')->hasTable('appointment_waitlist_entries')) {
                    DB::connection('tenant')->table('appointment_waitlist_entries')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('form_responses')) {
                    DB::connection('tenant')->table('form_responses')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('appointments')) {
                    DB::connection('tenant')->table('appointments')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('recurring_appointments')) {
                    DB::connection('tenant')->table('recurring_appointments')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('patient_logins')) {
                    DB::connection('tenant')->table('patient_logins')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                if (Schema::connection('tenant')->hasTable('patient_addresses')) {
                    DB::connection('tenant')->table('patient_addresses')
                        ->whereIn('patient_id', $chunk)
                        ->delete();
                }

                $deleted += DB::connection('tenant')->table('patients')
                    ->whereIn('id', $chunk)
                    ->delete();
            });
        }

        return $deleted;
    }
}
