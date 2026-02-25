<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ExpirePendingAppointmentsJob;
use Illuminate\Console\Command;

class ExpirePendingAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira agendamentos pendentes de confirmação cujo prazo venceu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $summary = app(ExpirePendingAppointmentsJob::class)->handle();

        $this->info(sprintf(
            'Tenants processados: %d | Agendamentos expirados: %d | Erros: %d',
            $summary['tenants'],
            $summary['expired'],
            $summary['errors']
        ));

        return Command::SUCCESS;
    }
}
