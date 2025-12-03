<?php

namespace App\Console\Commands;

use App\Models\Platform\User;
use Illuminate\Console\Command;

class AddNotificationTemplatesModuleToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-module {email=admin@plataforma.com : Email do usuário}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adiciona o módulo notification_templates a um usuário da Platform';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("Usuário com email '{$email}' não encontrado!");
                return 1;
            }
        } else {
            // Se não informou email, lista os usuários
            $users = User::all(['id', 'name', 'email']);
            
            if ($users->isEmpty()) {
                $this->error('Nenhum usuário encontrado!');
                return 1;
            }
            
            $this->info('Usuários disponíveis:');
            foreach ($users as $index => $u) {
                $this->line("  [{$index}] {$u->name} ({$u->email})");
            }
            
            $selected = $this->ask('Digite o email do usuário ou o número');
            
            // Tenta encontrar por email primeiro
            $user = User::where('email', $selected)->first();
            
            // Se não encontrou, tenta por índice
            if (!$user && is_numeric($selected)) {
                $userArray = $users->toArray();
                if (isset($userArray[$selected])) {
                    $user = User::find($userArray[$selected]['id']);
                }
            }
            
            if (!$user) {
                $this->error('Usuário não encontrado!');
                return 1;
            }
        }
        
        $modules = $user->modules ?? [];
        
        if (in_array('notification_templates', $modules)) {
            $this->warn("O usuário '{$user->name}' ({$user->email}) já possui o módulo 'notification_templates'!");
            return 0;
        }
        
        $modules[] = 'notification_templates';
        $user->modules = $modules;
        $user->save();
        
        $this->info("✅ Módulo 'notification_templates' adicionado com sucesso ao usuário '{$user->name}' ({$user->email})!");
        
        return 0;
    }
}
