<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\User;

class EnsurePlansModuleAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:ensure-plans-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Garante que todos os usuários tenham acesso ao módulo de planos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            $modules = $user->modules ?? [];
            
            if (!in_array('plans', $modules)) {
                $modules[] = 'plans';
                $user->update(['modules' => $modules]);
                $updated++;
                $this->info("✅ Módulo 'plans' adicionado ao usuário: {$user->email}");
            }
        }

        if ($updated === 0) {
            $this->info("✅ Todos os usuários já têm acesso ao módulo 'plans'");
        } else {
            $this->info("✅ Total de usuários atualizados: {$updated}");
        }

        return 0;
    }
}
