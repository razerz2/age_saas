<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\User;

class AddPreTenantsModuleToAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pre-tenants:add-module-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adiciona o módulo pre_tenants a todos os usuários da Platform';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adicionando módulo pre_tenants aos usuários...');

        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            $modules = $user->modules ?? [];
            
            // Adiciona o módulo se ainda não estiver presente
            if (!in_array('pre_tenants', $modules)) {
                $modules[] = 'pre_tenants';
                $user->update(['modules' => $modules]);
                $updated++;
                $this->line("✓ Módulo adicionado ao usuário: {$user->name} ({$user->email})");
            } else {
                $this->line("- Usuário {$user->name} já possui o módulo");
            }
        }

        $this->info("\n✅ Concluído! {$updated} usuário(s) atualizado(s).");
        
        return Command::SUCCESS;
    }
}
