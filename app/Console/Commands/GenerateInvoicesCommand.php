<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera faturas automaticamente X dias antes do vencimento (apenas PIX/Boleto, nunca no dia do vencimento)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 🔹 Obtém configuração do SystemSetting (default: 10 dias)
        $daysBefore = (int) (function_exists('sysconfig') 
            ? sysconfig('billing.invoice_days_before_due', 10)
            : 10);
        
        if ($daysBefore < 1) {
            $this->error('❌ O número de dias deve ser pelo menos 1.');
            return Command::FAILURE;
        }

        $this->info("🚀 Iniciando geração de faturas ({$daysBefore} dias antes do vencimento)...");

        $asaas = new AsaasService();
        $generated = 0;
        $skipped = 0;
        $errors = 0;

        // 🔹 Busca assinaturas ativas com auto_renew, apenas PIX/Boleto
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereIn('payment_method', ['PIX', 'BOLETO'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info("ℹ️ Nenhuma assinatura PIX/Boleto para processar.");
            return Command::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $tenant = $subscription->tenant;
                $plan = $subscription->plan;

                if (!$tenant || !$plan) {
                    Log::warning("⚠️ Assinatura {$subscription->id} sem tenant/plan associados.");
                    $skipped++;
                    continue;
                }

                // 🔹 Calcula próximo vencimento baseado no billing_anchor_date ou ends_at
                $anchorDate = $subscription->billing_anchor_date 
                    ? Carbon::parse($subscription->billing_anchor_date)
                    : ($subscription->ends_at ? Carbon::parse($subscription->ends_at) : now());

                // Próximo vencimento = anchor date + período do plano
                $nextDueDate = $anchorDate->copy()->addMonths($plan->period_months ?? 1);
                
                // Data de emissão = próximo vencimento - X dias
                $issueDate = $nextDueDate->copy()->subDays($daysBefore);
                
                // 🔹 REGRA CRÍTICA: Nunca emitir no dia do vencimento
                if ($issueDate->isSameDay($nextDueDate)) {
                    $issueDate = $nextDueDate->copy()->subDay();
                }

                // Só gera se hoje >= data de emissão e < data de vencimento
                $today = Carbon::today();
                if ($today->lt($issueDate) || $today->gte($nextDueDate)) {
                    continue;
                }

                // 🔹 Idempotência: verifica se já existe invoice pendente/overdue no mesmo período
                $existingInvoice = Invoices::where('subscription_id', $subscription->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->whereDate('due_date', $nextDueDate->toDateString())
                    ->first();

                if ($existingInvoice) {
                    Log::info("ℹ️ Fatura já existe para assinatura {$subscription->id} com vencimento {$nextDueDate->toDateString()}");
                    $skipped++;
                    continue;
                }

                // 🔹 Cria fatura (o InvoiceObserver enviará para o Asaas automaticamente)
                $invoice = Invoices::create([
                    'subscription_id' => $subscription->id,
                    'tenant_id'       => $tenant->id,
                    'amount_cents'    => $plan->price_cents,
                    'due_date'        => $nextDueDate->toDateString(),
                    'status'          => 'pending',
                    'payment_method'  => $subscription->payment_method,
                    'provider'        => 'asaas',
                ]);

                $generated++;
                Log::info("✅ Fatura gerada para assinatura {$subscription->id} (vencimento: {$nextDueDate->toDateString()})");

            } catch (\Throwable $e) {
                $errors++;
                Log::error("❌ Erro ao gerar fatura para assinatura {$subscription->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("✅ Processamento concluído:");
        $this->info("   - Faturas geradas: {$generated}");
        $this->info("   - Ignoradas: {$skipped}");
        $this->info("   - Erros: {$errors}");

        if ($generated > 0 || $errors > 0) {
            SystemNotificationService::notify(
                'Geração automática de faturas',
                "Foram geradas {$generated} faturas, {$skipped} ignoradas e {$errors} erros.",
                'invoice',
                $errors > 0 ? 'warning' : 'info'
            );
        }

        return Command::SUCCESS;
    }
}
