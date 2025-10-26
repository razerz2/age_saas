<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AsaasService;
use App\Models\Platform\Invoices;
use App\Models\Platform\Tenant;

class ClearTenantsAsaasCommand extends Command
{
    protected $signature = 'tenants:clear-asaas {--force : Força a exclusão sem confirmação}';
    protected $description = 'Apaga todos os clientes (tenants) no Asaas e suas faturas locais (modo testes).';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Tem certeza que deseja apagar TODOS os clientes (Asaas + Banco)?')) {
            $this->info('Operação cancelada.');
            return;
        }

        $asaas = new AsaasService();
        $deletedAsaas = 0;

        $this->info('🧹 Apagando clientes diretamente no Asaas...');

        try {
            $page = 0;
            do {
                $page++;
                $customers = $asaas->listCustomers($page);

                if (empty($customers['data'])) break;

                foreach ($customers['data'] as $customer) {
                    $resp = $asaas->deleteCustomer($customer['id']);

                    if (isset($resp['deleted']) && $resp['deleted'] === true) {
                        $deletedAsaas++;
                    } else {
                        $this->warn("⚠️ Falha ao apagar cliente {$customer['id']} ({$customer['name']}).");
                    }
                }
            } while (!empty($customers['hasMore']));

            $this->info("✅ {$deletedAsaas} clientes apagados do Asaas.");
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao apagar clientes no Asaas: " . $e->getMessage());
        }

        // 🔹 Agora limpa o banco local
        $this->newLine();
        $this->info('🧹 Limpando registros locais...');

        try {
            // primeiro faturas
            $invoiceCount = Invoices::count();
            Invoices::truncate();

            // depois tenants
            $tenantCount = Tenant::count();
            Tenant::truncate();

            $this->info("✅ {$invoiceCount} faturas locais removidas.");
            $this->info("✅ {$tenantCount} tenants locais removidos.");
        } catch (\Throwable $e) {
            $this->error("❌ Erro ao limpar dados locais: " . $e->getMessage());
        }

        // 📊 resumo final
        $this->newLine();
        $this->info('📊 Resumo da limpeza:');
        $this->line("• Clientes apagados do Asaas: {$deletedAsaas}");
        $this->line("• Faturas apagadas do banco local: {$invoiceCount}");
        $this->line("• Tenants apagados do banco local: {$tenantCount}");
        $this->newLine();
        $this->info('✨ Limpeza concluída com sucesso!');
    }
}
