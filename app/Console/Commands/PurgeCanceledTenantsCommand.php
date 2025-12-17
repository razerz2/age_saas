<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\SystemNotificationService;
use Carbon\Carbon;

class PurgeCanceledTenantsCommand extends Command
{
    protected $signature = 'tenants:purge-canceled {--dry-run : Executa sem fazer alterações, apenas mostra o que seria feito}';
    protected $description = 'Remove dados e banco de dados de tenants cancelados há ≥ 90 dias (com proteções)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        // 🔹 Obtém configuração do SystemSetting (default: 90 dias)
        $purgeDays = (int) (function_exists('sysconfig') 
            ? sysconfig('billing.purge_days_after_cancellation', 90)
            : 90);
        
        if ($dryRun) {
            $this->info("🔍 MODO DRY-RUN: Nenhuma alteração será feita");
        } else {
            $this->info("🗑️ Iniciando purga de tenants cancelados (≥ {$purgeDays} dias)...");
        }

        $purged = 0;
        $errors = 0;
        $skipped = 0;

        // 🔹 Busca tenants cancelados há ≥ X dias (configurável)
        $tenants = Tenant::where('status', 'canceled')
            ->whereNotNull('canceled_at')
            ->where('canceled_at', '<=', Carbon::now()->subDays($purgeDays))
            ->with(['subscriptions', 'invoices'])
            ->get();

        foreach ($tenants as $tenant) {
            try {
                // 🔹 PROTEÇÕES: Verifica se não tem assinaturas ativas ou pendentes
                $activeSubscriptions = $tenant->subscriptions()
                    ->whereIn('status', ['active', 'pending', 'recovery_pending'])
                    ->exists();

                if ($activeSubscriptions) {
                    Log::warning("⚠️ Tenant {$tenant->trade_name} tem assinaturas ativas/pendentes - pulando purga", [
                        'tenant_id' => $tenant->id,
                        'canceled_at' => $tenant->canceled_at,
                    ]);
                    $skipped++;
                    continue;
                }

                // 🔹 PROTEÇÕES: Verifica se não tem invoices pendentes
                $pendingInvoices = Invoices::where('tenant_id', $tenant->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->exists();

                if ($pendingInvoices) {
                    Log::warning("⚠️ Tenant {$tenant->trade_name} tem invoices pendentes - pulando purga", [
                        'tenant_id' => $tenant->id,
                    ]);
                    $skipped++;
                    continue;
                }

                $this->info("🗑️ Tenant {$tenant->trade_name} (cancelado em {$tenant->canceled_at->format('d/m/Y')})...");

                if ($dryRun) {
                    $this->info("   [DRY-RUN] Seria removido:");
                    $this->info("   - Banco: {$tenant->db_name}");
                    $this->info("   - Assinaturas: " . $tenant->subscriptions()->count());
                    $this->info("   - Faturas: " . Invoices::where('tenant_id', $tenant->id)->count());
                    $purged++;
                    continue;
                }

                // 🔹 1. Remove banco de dados do tenant
                if ($tenant->db_name) {
                    try {
                        // Desconecta todas as conexões ao banco
                        DB::statement("SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '{$tenant->db_name}' AND pid <> pg_backend_pid()");
                        
                        // Remove banco de dados
                        DB::statement("DROP DATABASE IF EXISTS \"{$tenant->db_name}\"");
                        
                        Log::info("🗑️ Banco de dados {$tenant->db_name} removido", [
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->trade_name,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning("⚠️ Erro ao remover banco {$tenant->db_name}: {$e->getMessage()}", [
                            'tenant_id' => $tenant->id,
                        ]);
                        // Continua mesmo se falhar (banco pode já ter sido removido)
                    }
                }

                // 🔹 2. Remove assinaturas e faturas (cascade já remove, mas garantimos)
                $subscriptionsCount = $tenant->subscriptions()->count();
                $invoicesCount = Invoices::where('tenant_id', $tenant->id)->count();
                
                $tenant->subscriptions()->delete();
                Invoices::where('tenant_id', $tenant->id)->delete();

                // 🔹 3. Remove tenant
                $tenantName = $tenant->trade_name;
                $tenant->delete();

                $purged++;
                Log::info("✅ Tenant {$tenantName} purgado completamente", [
                    'tenant_id' => $tenant->id,
                    'subscriptions_removed' => $subscriptionsCount,
                    'invoices_removed' => $invoicesCount,
                ]);

            } catch (\Throwable $e) {
                $errors++;
                Log::error("❌ Erro ao purgar tenant {$tenant->trade_name}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        $this->info("✅ Purga concluída:");
        $this->info("   - Tenants purgados: {$purged}");
        $this->info("   - Ignorados (proteções): {$skipped}");
        $this->info("   - Erros: {$errors}");

        if ($purged > 0 || $errors > 0) {
            SystemNotificationService::notify(
                'Purga de Tenants Cancelados',
                "Foram purgados {$purged} tenants cancelados há mais de 90 dias. {$skipped} ignorados (proteções), {$errors} erros.",
                'tenant',
                $errors > 0 ? 'warning' : 'info'
            );
        }

        return Command::SUCCESS;
    }
}
