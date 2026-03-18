<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Services\Platform\TenantDefaultNotificationTemplateProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Throwable;

class SeedDefaultWhatsappUnofficialTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed-default-whatsapp-unofficial-templates
                            {--tenant= : Slug (subdomain) ou UUID de tenant especifico}
                            {--apply : Persiste no banco. Sem esta flag, roda em dry-run}
                            {--overwrite : Atualiza templates ja existentes para as keys do baseline}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copia templates default nao oficiais (tenant_default_notification_templates) para notification_templates dos tenants.';

    public function __construct(
        private readonly TenantDefaultNotificationTemplateProvisioningService $provisioningService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantOption = trim((string) $this->option('tenant'));
        $apply = (bool) $this->option('apply');
        $overwrite = (bool) $this->option('overwrite');
        $dryRun = !$apply;

        $tenants = $this->resolveTenants($tenantOption);
        if ($tenants->isEmpty()) {
            return self::FAILURE;
        }

        $rows = [];
        $sumInserted = 0;
        $sumUpdated = 0;
        $sumSkipped = 0;
        $sumErrors = 0;

        foreach ($tenants as $tenant) {
            $status = 'ok';
            $detail = '-';

            try {
                $tenant->makeCurrent();
                $result = $this->provisioningService->syncForTenant($tenant, $overwrite, $dryRun);

                $inserted = (int) ($result['inserted'] ?? 0);
                $updated = (int) ($result['updated'] ?? 0);
                $skipped = (int) ($result['skipped'] ?? 0);
                $reason = $result['reason'] ?? null;

                $sumInserted += $inserted;
                $sumUpdated += $updated;
                $sumSkipped += $skipped;

                if ($reason) {
                    $status = 'aviso';
                    $detail = (string) $reason;
                }

                $rows[] = [
                    $tenant->subdomain ?: (string) $tenant->id,
                    (string) $inserted,
                    (string) $updated,
                    (string) $skipped,
                    $status,
                    $detail,
                ];
            } catch (Throwable $e) {
                $sumErrors++;
                $rows[] = [
                    $tenant->subdomain ?: (string) $tenant->id,
                    '0',
                    '0',
                    '0',
                    'erro',
                    $e->getMessage(),
                ];
            } finally {
                SpatieTenant::forgetCurrent();
            }
        }

        $this->newLine();
        $this->table(
            ['Tenant', 'Inseridos', 'Atualizados', 'Ignorados', 'Status', 'Detalhes'],
            $rows
        );

        $this->info('Resumo: inseridos=' . $sumInserted . ', atualizados=' . $sumUpdated . ', ignorados=' . $sumSkipped . ', erros=' . $sumErrors . '.');

        if ($dryRun) {
            $this->line('Dry-run concluido. Nenhuma alteracao persistida.');
            $command = 'php artisan tenants:seed-default-whatsapp-unofficial-templates';
            if ($tenantOption !== '') {
                $command .= ' --tenant=' . $tenantOption;
            }
            $command .= ' --apply';
            if ($overwrite) {
                $command .= ' --overwrite';
            }
            $this->line('Para aplicar: ' . $command);
        }

        return $sumErrors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return Collection<int, Tenant>
     */
    private function resolveTenants(string $tenantSlug): Collection
    {
        if ($tenantSlug === '') {
            return Tenant::query()->orderBy('subdomain')->get();
        }

        $query = Tenant::query()->where('subdomain', $tenantSlug);
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
}
