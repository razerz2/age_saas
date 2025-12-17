<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Models\Tenant\FinancialCharge;
use App\Services\Finance\AsaasService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinanceReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:reconcile 
                            {--tenant= : Slug do tenant específico}
                            {--from= : Data inicial (Y-m-d)}
                            {--to= : Data final (Y-m-d)}
                            {--force : Forçar reconciliação mesmo se já processado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcilia cobranças financeiras com o Asaas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantSlug = $this->option('tenant');
        $fromDate = $this->option('from') ? Carbon::parse($this->option('from')) : now()->subDays(30);
        $toDate = $this->option('to') ? Carbon::parse($this->option('to')) : now();
        $force = $this->option('force');

        $tenants = $tenantSlug 
            ? [Tenant::where('subdomain', $tenantSlug)->firstOrFail()]
            : Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant: {$tenant->subdomain}");

            $tenant->makeCurrent();

            // Verificar se módulo está habilitado
            if (tenant_setting('finance.enabled') !== 'true') {
                $this->warn("  Módulo financeiro desabilitado, pulando...");
                continue;
            }

            // Buscar cobranças pendentes ou inconsistentes
            $query = FinancialCharge::whereNotNull('asaas_charge_id')
                ->whereBetween('created_at', [$fromDate, $toDate]);

            if (!$force) {
                $query->whereIn('status', ['pending', 'overdue']);
            }

            $charges = $query->get();

            $this->info("  Encontradas {$charges->count()} cobranças para reconciliar");

            $reconciled = 0;
            $errors = 0;

            foreach ($charges as $charge) {
                try {
                    $this->reconcileCharge($charge, $force);
                    $reconciled++;
                    $this->line("  ✓ Charge {$charge->id} reconciliada");
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("  ✗ Erro ao reconciliar charge {$charge->id}: {$e->getMessage()}");
                    Log::error('Erro ao reconciliar charge', [
                        'charge_id' => $charge->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("  Reconciliadas: {$reconciled}, Erros: {$errors}");
        }

        $this->info('Reconciliação concluída!');
        return Command::SUCCESS;
    }

    /**
     * Reconcilia uma cobrança específica
     */
    protected function reconcileCharge(FinancialCharge $charge, bool $force): void
    {
        if (!$charge->asaas_charge_id) {
            throw new \Exception('Charge sem asaas_charge_id');
        }

        $asaasService = new AsaasService();

        // Buscar status real no Asaas
        $paymentData = $asaasService->getPayment($charge->asaas_charge_id);

        if (!$paymentData) {
            throw new \Exception('Não foi possível obter dados do pagamento no Asaas');
        }

        $asaasStatus = strtoupper($paymentData['status'] ?? 'PENDING');
        $internalStatus = $this->mapAsaasStatus($asaasStatus);

        // Se status mudou, atualizar
        if ($force || $charge->status !== $internalStatus) {
            $charge->update(['status' => $internalStatus]);

            // Se foi pago, processar transação e comissão
            if ($internalStatus === 'paid' && ($force || $charge->status !== 'paid')) {
                $this->processPaidCharge($charge, $paymentData);
            }
        }
    }

    /**
     * Mapeia status do Asaas para status interno
     */
    protected function mapAsaasStatus(string $asaasStatus): string
    {
        return match($asaasStatus) {
            'PENDING' => 'pending',
            'RECEIVED', 'CONFIRMED' => 'paid',
            'OVERDUE' => 'overdue',
            'REFUNDED', 'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Processa cobrança paga
     */
    protected function processPaidCharge(FinancialCharge $charge, array $paymentData): void
    {
        // Usar os serviços de conciliação
        $chargeService = app(\App\Services\Finance\Reconciliation\ChargeReconciliationService::class);
        $transactionService = app(\App\Services\Finance\Reconciliation\TransactionReconciliationService::class);
        $commissionService = app(\App\Services\Finance\Reconciliation\CommissionReconciliationService::class);

        $chargeService->reconcilePaid($charge, ['payment' => $paymentData]);
        $transactionService->reconcileFromCharge($charge);
        $commissionService->reconcileFromCharge($charge);
    }
}

