<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ExpireWaitlistOffersJob;
use Illuminate\Console\Command;

class ExpireWaitlistOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:expire-waitlist-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira ofertas de waitlist vencidas e oferta o slot para o próximo paciente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $summary = app(ExpireWaitlistOffersJob::class)->handle();

        $this->info(sprintf(
            'Tenants processados: %d | Ofertas expiradas: %d | Novas ofertas: %d | Erros: %d',
            $summary['tenants'],
            $summary['expired'],
            $summary['offered'],
            $summary['errors']
        ));

        return Command::SUCCESS;
    }
}

