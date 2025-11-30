<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;

class AddModuleToUser extends Command
{
    protected $signature = 'tenant:add-module {tenant} {user_id} {module}';
    protected $description = 'Adiciona um módulo a um usuário do tenant';

    public function handle()
    {
        $tenantSlug = $this->argument('tenant');
        $userId = $this->argument('user_id');
        $module = $this->argument('module');

        try {
            // Busca e ativa o tenant
            $tenant = Tenant::where('subdomain', $tenantSlug)->first();
            
            if (!$tenant) {
                $this->error("❌ Tenant '{$tenantSlug}' não encontrado!");
                return 1;
            }
            
            $tenant->makeCurrent();
            $this->info("✅ Tenant '{$tenantSlug}' ativado");
            
            // Busca o usuário
            $user = User::findOrFail($userId);
            
            $modules = $user->modules ?? [];
            
            if (!in_array($module, $modules)) {
                $modules[] = $module;
                $user->modules = $modules;
                $user->save();
                
                $this->info("✅ Módulo '{$module}' adicionado ao usuário ID {$userId} com sucesso!");
                $this->info("Módulos atuais: " . implode(', ', $user->modules));
            } else {
                $this->warn("⚠️ O usuário já possui o módulo '{$module}'");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        }
    }
}

