<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;

class ResetTenantAdminPassword extends Command
{
    protected $signature = 'tenant:reset-admin-password {subdomain} {--password=admin123}';
    protected $description = 'Redefine a senha do usuário admin de um tenant';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $newPassword = $this->option('password');
        
        $this->info("🔐 Redefinindo senha do admin para tenant: {$subdomain}");
        $this->newLine();

        // 1. Buscar tenant
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        
        if (!$tenant) {
            $this->error("❌ Tenant não encontrado!");
            return 1;
        }

        // 2. Ativar tenant
        $tenant->makeCurrent();
        $this->info("✅ Tenant ativado");

        // 3. Buscar usuário admin
        $domain = preg_replace('/[^a-z0-9\-]/', '', \Illuminate\Support\Str::slug($tenant->subdomain));
        $adminEmail = "admin@{$domain}.com";
        
        $user = User::on('tenant')->where('email', $adminEmail)->first();
        
        if (!$user) {
            $this->error("❌ Usuário admin não encontrado com email: {$adminEmail}");
            $this->warn("💡 Listando todos os usuários...");
            
            $users = User::on('tenant')->get(['id', 'name', 'email', 'role']);
            foreach ($users as $u) {
                $this->line("   - {$u->email} (Role: {$u->role})");
            }
            
            return 1;
        }

        $this->info("✅ Usuário admin encontrado:");
        $this->line("   ID: {$user->id}");
        $this->line("   Nome: {$user->name}");
        $this->line("   Email: {$user->email}");
        $this->newLine();

        // 4. Confirmar ação
        if (!$this->confirm("Deseja realmente redefinir a senha para '{$newPassword}'?")) {
            $this->info("Operação cancelada.");
            return 0;
        }

        // 5. Atualizar senha
        $user->password = Hash::make($newPassword);
        $user->save();

        $this->info("✅ Senha redefinida com sucesso!");
        $this->newLine();
        $this->line("📋 Credenciais de acesso:");
        $this->line("   Email: {$user->email}");
        $this->line("   Senha: {$newPassword}");
        $this->newLine();
        $this->info("💡 Agora você pode fazer login em:");
        $this->line("   /t/{$subdomain}/login");

        return 0;
    }
}

