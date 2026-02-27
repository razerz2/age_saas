<?php

namespace App\Console\Commands\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use Database\Factories\Tenant\PatientFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class SeedFakePatients extends Command
{
    protected $signature = 'tenant:patients:seed
                            {--tenant= : Slug (subdomain) de um tenant especifico}
                            {--all-tenants : Executa em todos os tenants}
                            {--count=50 : Quantidade de pacientes por tenant}
                            {--tag=test : Tag para marcacao de pacientes de teste}
                            {--force : Permite execucao em producao}';

    protected $description = 'Gera pacientes ficticios (PT-BR) nos tenants selecionados.';

    public function handle(): int
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Execucao bloqueada em producao. Use --force para confirmar.');
            return self::FAILURE;
        }

        $count = max(0, (int) $this->option('count'));
        if ($count < 1) {
            $this->error('Informe um --count valido (>= 1).');
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

        $globalStart = microtime(true);
        $rows = [];

        foreach ($tenants as $tenant) {
            $tenantStart = microtime(true);
            $created = 0;
            $status = 'ok';
            $message = '-';

            try {
                $tenant->makeCurrent();

                if (!Schema::connection('tenant')->hasTable('patients')) {
                    throw new \RuntimeException('Tabela patients nao encontrada no banco tenant.');
                }

                $columns = Schema::connection('tenant')->getColumnListing('patients');
                if (!$this->hasAnyTestMarkerColumn($columns)) {
                    throw new \RuntimeException(
                        'Nenhuma coluna de marcacao disponivel (is_test/test_tag/metadata/notes/tags). Rode migrations tenant.'
                    );
                }

                $genderIds = $this->resolveGenderIds($columns);
                $factory = PatientFactory::new();
                $batch = [];

                for ($i = 0; $i < $count; $i++) {
                    $attributes = $factory->raw();

                    if (in_array('gender_id', $columns, true)) {
                        $attributes['gender_id'] = !empty($genderIds) ? Arr::random($genderIds) : null;
                    }

                    $this->applyTestMarker($attributes, $columns, $tag);
                    $batch[] = Arr::only($attributes, $columns);

                    if (count($batch) === 500) {
                        DB::connection('tenant')->table('patients')->insert($batch);
                        $created += count($batch);
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    DB::connection('tenant')->table('patients')->insert($batch);
                    $created += count($batch);
                }
            } catch (Throwable $exception) {
                $status = 'erro';
                $message = $exception->getMessage();
                $this->error("[{$tenant->subdomain}] falha ao gerar pacientes: {$message}");
            } finally {
                SpatieTenant::forgetCurrent();
            }

            $elapsed = number_format(microtime(true) - $tenantStart, 2);
            $rows[] = [
                $tenant->subdomain ?: $tenant->id,
                (string) $created,
                $status,
                "{$elapsed}s",
                $message,
            ];
        }

        $this->newLine();
        $this->table(['Tenant', 'Criados', 'Status', 'Tempo', 'Detalhes'], $rows);
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

    private function resolveGenderIds(array $patientColumns): array
    {
        if (!in_array('gender_id', $patientColumns, true)) {
            return [];
        }

        if (!Schema::connection('tenant')->hasTable('genders')) {
            return [];
        }

        $query = DB::connection('tenant')->table('genders');
        $ids = $query->where('is_active', true)->pluck('id')->all();

        if (!empty($ids)) {
            return $ids;
        }

        return $query->pluck('id')->all();
    }

    private function applyTestMarker(array &$attributes, array $columns, string $tag): void
    {
        if (in_array('is_test', $columns, true)) {
            $attributes['is_test'] = true;
        }

        if (in_array('test_tag', $columns, true)) {
            $attributes['test_tag'] = $tag;
            return;
        }

        if (in_array('metadata', $columns, true)) {
            $attributes['metadata'] = json_encode([
                'is_test' => true,
                'test_tag' => $tag,
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (in_array('notes', $columns, true)) {
            $attributes['notes'] = trim((string) ($attributes['notes'] ?? '') . ' [test:' . $tag . ']');
            return;
        }

        if (in_array('tags', $columns, true)) {
            $attributes['tags'] = $tag;
        }
    }

    private function hasAnyTestMarkerColumn(array $columns): bool
    {
        foreach (['is_test', 'test_tag', 'metadata', 'notes', 'tags'] as $column) {
            if (in_array($column, $columns, true)) {
                return true;
            }
        }

        return false;
    }
}
