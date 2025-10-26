<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AsaasService;

class ClearInvoicesAsaasCommand extends Command
{
    protected $signature = 'invoices:clear-asaas-invoices {--force : Força a exclusão sem confirmação}';
    protected $description = 'Apaga TODAS as faturas diretamente no Asaas (modo manutenção/testes).';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Tem certeza que deseja apagar TODAS as faturas no Asaas?')) {
            $this->info('Operação cancelada.');
            return;
        }

        $asaas = new AsaasService();
        $deleted = 0;
        $page = 0;

        $this->info('🧹 Apagando faturas diretamente no Asaas...');

        try {
            do {
                $page++;
                $payments = $asaas->listPayments($page);

                if (empty($payments['data'])) break;

                foreach ($payments['data'] as $payment) {
                    $resp = $asaas->deletePayment($payment['id']);

                    if (isset($resp['deleted']) && $resp['deleted'] === true) {
                        $deleted++;
                    } else {
                        $this->warn("⚠️ Falha ao apagar fatura {$payment['id']}.");
                    }
                }
            } while (!empty($payments['hasMore']));

            $this->info("✅ {$deleted} faturas apagadas com sucesso do Asaas.");
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao apagar faturas: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('✨ Limpeza completa no Asaas.');
    }
}
