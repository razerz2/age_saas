<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Services\SystemNotificationService;
use Carbon\Carbon;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:invoices-check-overdue';
    protected $description = 'Marca faturas vencidas e suspende tenants imediatamente (sem período de carência).';

    public function handle()
    {
        $this->info("🔎 Verificando faturas vencidas (suspensão imediata, sem carência)...");

        $suspended = 0;
        $markedOverdue = 0;

        // 🔹 1. Marca como overdue todas as faturas pending com due_date no passado
        $pendingOverdue = Invoices::where('status', 'pending')
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        foreach ($pendingOverdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $markedOverdue++;
            Log::info("📅 Fatura {$invoice->id} marcada como overdue (vencida em {$invoice->due_date->format('d/m/Y')})");
        }

        // 🔹 2. Suspende imediatamente todos os tenants com faturas overdue
        $overdueInvoices = Invoices::where('status', 'overdue')
            ->with('tenant')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $tenant = $invoice->tenant;
            
            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update([
                    'status' => 'suspended',
                    'suspended_at' => now(),
                ]);
                $suspended++;
                
                // Atualiza status da assinatura
                if ($invoice->subscription) {
                    $invoice->subscription->update(['status' => 'past_due']);
                }
                
                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso imediatamente por fatura vencida (ID: {$invoice->id}, vencimento: {$invoice->due_date->format('d/m/Y')})");
            }
        }

        if ($markedOverdue > 0 || $suspended > 0) {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                "Foram marcadas {$markedOverdue} faturas como vencidas e {$suspended} tenants foram suspensos imediatamente (sem período de carência).",
                'invoice',
                'warning'
            );
        } else {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                'Nenhuma fatura vencida encontrada.',
                'invoice',
                'info'
            );
        }

        $this->info("✅ Verificação concluída: {$markedOverdue} faturas marcadas como overdue, {$suspended} tenants suspensos.");
        return Command::SUCCESS;
    }
}
