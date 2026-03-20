<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateInvoicesCommand extends Command
{
    public function __construct(
        private readonly WhatsAppOfficialMessageService $officialWhatsApp
    ) {
        parent::__construct();
    }

    protected $signature = 'invoices:generate';
    protected $description = 'Gera faturas automaticamente X dias antes do vencimento (apenas PIX/Boleto, nunca no dia do vencimento)';

    public function handle()
    {
        $daysBefore = (int) (function_exists('sysconfig')
            ? sysconfig('billing.invoice_days_before_due', 10)
            : 10);

        if ($daysBefore < 1) {
            $this->error('O numero de dias deve ser pelo menos 1.');
            return Command::FAILURE;
        }

        $this->info("Iniciando geracao de faturas ({$daysBefore} dias antes do vencimento)...");

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->where(function ($query) {
                $query->whereNull('is_trial')
                    ->orWhere('is_trial', false);
            })
            ->whereHas('plan', function ($query) {
                $query->where('plan_type', Plan::TYPE_REAL);
            })
            ->whereIn('payment_method', ['PIX', 'BOLETO'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('Nenhuma assinatura PIX/Boleto para processar.');
            return Command::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $tenant = $subscription->tenant;
                $plan = $subscription->plan;

                if (! $tenant || ! $plan) {
                    Log::warning("Assinatura {$subscription->id} sem tenant/plan associados.");
                    $skipped++;
                    continue;
                }

                if ($subscription->is_trial) {
                    Log::info("Assinatura {$subscription->id} ignorada: trial comercial nao gera fatura.");
                    $skipped++;
                    continue;
                }

                if ($plan->isTest()) {
                    Log::info("Assinatura {$subscription->id} ignorada: plano de teste nao participa de faturamento.");
                    $skipped++;
                    continue;
                }

                $anchorDate = $subscription->billing_anchor_date
                    ? Carbon::parse($subscription->billing_anchor_date)
                    : ($subscription->ends_at ? Carbon::parse($subscription->ends_at) : now());

                $nextDueDate = $anchorDate->copy()->addMonths($plan->period_months ?? 1);
                $issueDate = $nextDueDate->copy()->subDays($daysBefore);

                if ($issueDate->isSameDay($nextDueDate)) {
                    $issueDate = $nextDueDate->copy()->subDay();
                }

                $today = Carbon::today();
                if ($today->lt($issueDate) || $today->gte($nextDueDate)) {
                    continue;
                }

                $existingInvoice = Invoices::where('subscription_id', $subscription->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->whereDate('due_date', $nextDueDate->toDateString())
                    ->first();

                if ($existingInvoice) {
                    Log::info("Fatura ja existe para assinatura {$subscription->id} com vencimento {$nextDueDate->toDateString()}");
                    $skipped++;
                    continue;
                }

                $invoice = Invoices::create([
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $tenant->id,
                    'amount_cents' => $plan->price_cents,
                    'due_date' => $nextDueDate->toDateString(),
                    'status' => 'pending',
                    'payment_method' => $subscription->payment_method,
                    'provider' => 'asaas',
                ]);

                $this->officialWhatsApp->sendByKey(
                    'invoice.created',
                    $tenant->phone,
                    [
                        'customer_name' => $tenant->trade_name,
                        'tenant_name' => $tenant->trade_name,
                        'invoice_amount' => 'R$ ' . number_format($invoice->amount_cents / 100, 2, ',', '.'),
                        'due_date' => Carbon::parse($invoice->due_date)->format('d/m/Y'),
                        'payment_link' => trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas')),
                    ],
                    [
                        'command' => static::class,
                        'invoice_id' => (string) $invoice->id,
                        'tenant_id' => (string) $tenant->id,
                        'event' => 'invoice.created',
                    ]
                );

                $generated++;
                Log::info("Fatura gerada para assinatura {$subscription->id} (vencimento: {$nextDueDate->toDateString()})");
            } catch (\Throwable $e) {
                $errors++;
                Log::error("Erro ao gerar fatura para assinatura {$subscription->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info('Processamento concluido:');
        $this->info("   - Faturas geradas: {$generated}");
        $this->info("   - Ignoradas: {$skipped}");
        $this->info("   - Erros: {$errors}");

        if ($generated > 0 || $errors > 0) {
            SystemNotificationService::notify(
                'Geracao automatica de faturas',
                "Foram geradas {$generated} faturas, {$skipped} ignoradas e {$errors} erros.",
                'invoice',
                $errors > 0 ? 'warning' : 'info'
            );
        }

        return Command::SUCCESS;
    }
}
