<?php

namespace App\Console\Commands;

use App\Models\Platform\Invoices;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Services\SystemNotificationService;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:invoices-check-overdue';
    protected $description = 'Verifica faturas vencidas e suspende tenants em atraso.';

    public function handle()
    {
        $this->info("🔎 Verificando faturas vencidas há mais de 5 dias...");

        $overdue = Invoices::where('status', 'overdue')
            ->whereDate('due_date', '<=', now()->subDays(5))
            ->get();

        foreach ($overdue as $invoice) {
            $tenant = $invoice->tenant;
            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update(['status' => 'suspended']);
                Log::warning("⛔ Tenant {$tenant->trade_name} suspenso automaticamente por fatura vencida há mais de 5 dias.");
            }
        }

        $affectedTenants = $overdue->count();

        if ($affectedTenants > 0) {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                "Foram encontradas {$affectedTenants} faturas vencidas há mais de 5 dias. Os tenants correspondentes foram suspensos automaticamente.",
                'invoice',
                'warning'
            );
        } else {
            SystemNotificationService::notify(
                'Verificação de faturas vencidas',
                'Nenhuma fatura vencida há mais de 5 dias foi encontrada.',
                'invoice',
                'info'
            );
        }

        $this->info("✅ Verificação concluída.");
        return Command::SUCCESS;
    }
}
