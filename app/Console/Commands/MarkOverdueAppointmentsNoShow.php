<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class MarkOverdueAppointmentsNoShow extends Command
{
    private const DEFAULT_GRACE_MINUTES = 15;
    private const HISTORY_WINDOW_DAYS = 30;

    protected $signature = 'appointments:mark-overdue
                            {--tenant=* : Tenant id/subdomain (pode repetir)}
                            {--dry-run : Apenas simula sem alterar dados}
                            {--grace=15 : Minutos de carência após ends_at}';

    protected $description = 'Marca appointments vencidos (scheduled/rescheduled) como no_show';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $graceMinutes = self::normalizeGraceMinutes($this->option('grace'));
        $tenants = $this->resolveTenants((array) $this->option('tenant'));

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado para processar.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Processando %d tenant(s)%s | grace=%dmin | janela=%dd',
            $tenants->count(),
            $dryRun ? ' (dry-run)' : '',
            $graceMinutes,
            self::HISTORY_WINDOW_DAYS
        ));

        $totals = [
            'tenants' => 0,
            'candidates' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);
                $totals['tenants']++;

                $tenantTimezone = $this->resolveTenantTimezone();
                $storageTimezone = $this->resolveStorageTimezone();

                $nowTenant = Carbon::now($tenantTimezone);
                $cutoffTenant = $nowTenant->copy()->subMinutes($graceMinutes);
                $windowStartTenant = $nowTenant->copy()->subDays(self::HISTORY_WINDOW_DAYS);

                $cutoffForQuery = $cutoffTenant->copy()->timezone($storageTimezone);
                $windowStartForQuery = $windowStartTenant->copy()->timezone($storageTimezone);

                $query = Appointment::query()
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->where('ends_at', '<', $cutoffForQuery)
                    ->where('ends_at', '>=', $windowStartForQuery);

                $candidates = (clone $query)->count();
                $updated = 0;

                if (!$dryRun && $candidates > 0) {
                    $updated = $query->update([
                        'status' => 'no_show',
                        'updated_at' => Carbon::now($storageTimezone),
                    ]);
                }

                $totals['candidates'] += $candidates;
                $totals['updated'] += $updated;

                Log::info('appointments_mark_overdue_processed', [
                    'tenant_id' => $tenant->id,
                    'tenant_subdomain' => $tenant->subdomain,
                    'tenant_timezone' => $tenantTimezone,
                    'storage_timezone' => $storageTimezone,
                    'grace_minutes' => $graceMinutes,
                    'dry_run' => $dryRun,
                    'candidates' => $candidates,
                    'updated' => $updated,
                    'cutoff_tenant' => $cutoffTenant->toIso8601String(),
                    'cutoff_storage' => $cutoffForQuery->toDateTimeString(),
                ]);

                $this->line(sprintf(
                    '[%s] tz=%s candidates=%d updated=%d',
                    $tenant->subdomain ?: $tenant->id,
                    $tenantTimezone,
                    $candidates,
                    $updated
                ));
            } catch (Throwable $exception) {
                $totals['errors']++;

                Log::error('appointments_mark_overdue_failed', [
                    'tenant_id' => $tenant->id,
                    'tenant_subdomain' => $tenant->subdomain,
                    'error' => $exception->getMessage(),
                ]);

                $this->error(sprintf(
                    '[%s] falha ao processar tenant: %s',
                    $tenant->subdomain ?: $tenant->id,
                    $exception->getMessage()
                ));
            } finally {
                tenancy()->end();
            }
        }

        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Tenants processados', (string) $totals['tenants']],
                ['Candidatos avaliados', (string) $totals['candidates']],
                ['Atualizados para no_show', (string) $totals['updated']],
                ['Erros por tenant', (string) $totals['errors']],
            ]
        );

        return self::SUCCESS;
    }

    public static function normalizeGraceMinutes(mixed $value): int
    {
        $grace = filter_var($value, FILTER_VALIDATE_INT);
        if ($grace === false || $grace === null) {
            return self::DEFAULT_GRACE_MINUTES;
        }

        return max(0, (int) $grace);
    }

    public static function normalizeTimezone(mixed $timezone, ?string $fallback = null): string
    {
        $safeFallback = is_string($fallback) && trim($fallback) !== ''
            ? trim($fallback)
            : (string) config('app.timezone', 'America/Sao_Paulo');

        $candidate = is_string($timezone) ? trim($timezone) : '';
        if ($candidate === '') {
            return $safeFallback;
        }

        try {
            new \DateTimeZone($candidate);
            return $candidate;
        } catch (Throwable) {
            return $safeFallback;
        }
    }

    /**
     * @param array<int,mixed> $tenantFilters
     * @return Collection<int,Tenant>
     */
    private function resolveTenants(array $tenantFilters): Collection
    {
        $filters = collect($tenantFilters)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values();

        $tenants = Tenant::query()
            ->where('status', 'active')
            ->orderBy('subdomain')
            ->get();

        if ($filters->isEmpty()) {
            return $tenants;
        }

        return $tenants->filter(function (Tenant $tenant) use ($filters) {
            return $filters->contains((string) $tenant->id)
                || $filters->contains((string) $tenant->subdomain);
        })->values();
    }

    private function resolveTenantTimezone(): string
    {
        $timezone = tenant_setting('timezone', $this->resolveStorageTimezone());
        return self::normalizeTimezone($timezone, $this->resolveStorageTimezone());
    }

    private function resolveStorageTimezone(): string
    {
        // No projeto atual, appointments não são normalizados para UTC na gravação
        // (timestamp sem timezone + sem mutator UTC). Por padrão, comparar no timezone
        // da aplicação. Se a conexão tenant declarar timezone UTC, usar UTC.
        $connectionTimezone = config('database.connections.tenant.timezone');
        if (is_string($connectionTimezone) && trim($connectionTimezone) !== '') {
            $normalizedConnectionTimezone = strtoupper(trim($connectionTimezone));
            if (in_array($normalizedConnectionTimezone, ['UTC', '+00:00', 'Z'], true)) {
                return 'UTC';
            }

            return self::normalizeTimezone($connectionTimezone);
        }

        return self::normalizeTimezone(config('app.timezone', 'America/Sao_Paulo'));
    }
}
