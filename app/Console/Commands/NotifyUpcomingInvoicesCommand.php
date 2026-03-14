<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Invoices;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyUpcomingInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:notify-upcoming';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica tenants sobre faturas próximas do vencimento (exclui faturas de cartão)';

    protected WhatsAppOfficialMessageService $officialWhatsApp;

    public function __construct(WhatsAppOfficialMessageService $officialWhatsApp)
    {
        parent::__construct();
        $this->officialWhatsApp = $officialWhatsApp;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 🔹 Obtém configuração do SystemSetting (default: 5 dias)
        $daysBefore = (int) (function_exists('sysconfig') 
            ? sysconfig('billing.notify_days_before_due', 5)
            : 5);
        
        if ($daysBefore < 1) {
            $this->error('❌ O número de dias deve ser pelo menos 1.');
            return Command::FAILURE;
        }

        $this->info("🚀 Iniciando notificações de faturas próximas do vencimento ({$daysBefore} dias antes)...");

        $notified = 0;
        $skipped = 0;
        $errors = 0;

        $targetDate = Carbon::today()->addDays($daysBefore);

        // 🔹 Busca faturas pendentes que vencem em Y dias
        // 🔹 REGRA CRÍTICA: Exclui faturas de cartão (CREDIT_CARD, DEBIT_CARD)
        // 🔹 Não notifica paid/canceled/overdue
        $invoices = Invoices::with(['tenant', 'subscription'])
            ->where('status', 'pending')
            ->whereDate('due_date', $targetDate->toDateString())
            ->whereNotIn('payment_method', ['CREDIT_CARD', 'DEBIT_CARD'])
            ->get();

        if ($invoices->isEmpty()) {
            $this->info("ℹ️ Nenhuma fatura PIX/Boleto para notificar hoje.");
            return Command::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            try {
                $tenant = $invoice->tenant;
                
                if (!$tenant) {
                    Log::warning("⚠️ Fatura {$invoice->id} sem tenant associado.");
                    $skipped++;
                    continue;
                }

                // Verifica se tem telefone cadastrado
                if (empty($tenant->phone)) {
                    Log::info("ℹ️ Tenant {$tenant->trade_name} sem telefone cadastrado - notificação ignorada.");
                    $skipped++;
                    continue;
                }

                // 🔹 Deduplicação: verifica se já foi notificado hoje
                if ($invoice->notified_upcoming_at && 
                    Carbon::parse($invoice->notified_upcoming_at)->isToday()) {
                    Log::info("ℹ️ Fatura {$invoice->id} já foi notificada hoje - ignorando.");
                    $skipped++;
                    continue;
                }

                $amount = number_format($invoice->amount_cents / 100, 2, ',', '.');
                $dueDate = Carbon::parse($invoice->due_date)->format('d/m/Y');
                $paymentLink = trim((string) ($invoice->payment_link ?: 'https://app.allsync.com.br/faturas'));

                $sent = $this->officialWhatsApp->sendByKey(
                    'invoice.upcoming_due',
                    $tenant->phone,
                    [
                        'customer_name' => $tenant->trade_name,
                        'tenant_name' => $tenant->trade_name,
                        'due_date' => $dueDate,
                        'invoice_amount' => 'R$ ' . $amount,
                        'payment_link' => $paymentLink,
                    ],
                    [
                        'command' => static::class,
                        'invoice_id' => (string) $invoice->id,
                        'tenant_id' => (string) $tenant->id,
                        'event' => 'invoice.upcoming_due',
                    ]
                );

                if ($sent) {
                    // 🔹 Marca notified_upcoming_at para deduplicação
                    $invoice->update(['notified_upcoming_at' => now()]);
                    $notified++;
                    Log::info("✅ Notificação enviada para tenant {$tenant->trade_name} sobre fatura {$invoice->id}");
                } else {
                    $errors++;
                    Log::warning("⚠️ Falha ao enviar notificação WhatsApp para tenant {$tenant->trade_name}");
                }

            } catch (\Throwable $e) {
                $errors++;
                Log::error("❌ Erro ao notificar sobre fatura {$invoice->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("✅ Processamento concluído:");
        $this->info("   - Notificações enviadas: {$notified}");
        $this->info("   - Ignoradas: {$skipped}");
        $this->info("   - Erros: {$errors}");

        if ($notified > 0 || $errors > 0) {
            SystemNotificationService::notify(
                'Notificações de faturas próximas',
                "Foram enviadas {$notified} notificações, {$skipped} ignoradas e {$errors} erros.",
                'invoice',
                $errors > 0 ? 'warning' : 'info'
            );
        }

        return Command::SUCCESS;
    }
}
