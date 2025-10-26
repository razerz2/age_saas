<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Invoices;
use App\Services\AsaasService;

class ClearInvoicesCommand extends Command
{
    protected $signature = 'invoices:invoices-clear {--force : Força a exclusão sem confirmação}';
    protected $description = 'Apaga todas as faturas do Asaas e do banco local (modo testes).';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Tem certeza que deseja apagar TODAS as faturas (Asaas + Banco)?')) {
            $this->info('Operação cancelada.');
            return;
        }

        $asaas = new AsaasService();
        $deletedRemote = 0;

        // 🧹 1️⃣ Apagar faturas no Asaas primeiro
        $this->info('🧹 Apagando faturas no Asaas...');

        try {
            $page = 0;
            do {
                $page++;
                $payments = $asaas->listPayments($page);

                if (empty($payments['data'])) break;

                foreach ($payments['data'] as $payment) {
                    $resp = $asaas->deletePayment($payment['id']);

                    if (isset($resp['deleted']) && $resp['deleted'] === true) {
                        $deletedRemote++;
                    }
                }
            } while (!empty($payments['hasMore']));

            $this->info("✅ {$deletedRemote} faturas apagadas do Asaas.");
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao apagar faturas no Asaas: " . $e->getMessage());
        }

        // 🧹 2️⃣ Agora apagar localmente
        $this->info('🧹 Apagando faturas locais...');
        $countLocal = Invoices::count();
        Invoices::truncate();
        $this->info("✅ {$countLocal} faturas locais removidas.");

        // 📊 3️⃣ Resumo final
        $this->newLine();
        $this->info('📊 Resumo da limpeza:');
        $this->line("• Faturas apagadas do Asaas: {$deletedRemote}");
        $this->line("• Faturas apagadas do banco local: {$countLocal}");
        $this->newLine();
        $this->info('✨ Limpeza concluída com sucesso!');
    }
}
