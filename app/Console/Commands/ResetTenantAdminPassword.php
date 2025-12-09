<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetTenantAdminPassword extends Command
{
    protected $signature = 'tenant:reset-admin-password {subdomain} {--password=} {--email=}';
    protected $description = 'Redefine a senha do usuário admin de um tenant';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $newPassword = $this->option('password');
        $adminEmailOption = $this->option('email');
        
        $this->info("🔐 Redefinindo senha do admin para tenant: {$subdomain}");
        $this->newLine();

        // 1. Buscar tenant
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        
        if (!$tenant) {
            // Tentar buscar pelo email do admin se fornecido
            if ($adminEmailOption) {
                $tenant = Tenant::where('admin_email', $adminEmailOption)->first();
            }
            
            if (!$tenant) {
                $this->error("❌ Tenant não encontrado!");
                $this->warn("💡 Tenants disponíveis:");
                $tenants = Tenant::all(['id', 'subdomain', 'legal_name', 'admin_email']);
                foreach ($tenants as $t) {
                    $this->line("   - {$t->subdomain} ({$t->legal_name}) - Admin: {$t->admin_email}");
                }
                return 1;
            }
        }

        // 2. Ativar tenant
        $tenant->makeCurrent();
        $this->info("✅ Tenant ativado");

        // 3. Buscar usuário admin pelo email salvo ou gerar dinamicamente
        $adminEmail = $tenant->admin_email;
        if (!$adminEmail) {
            $domain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $adminEmail = "admin@{$domain}.com";
        }
        
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

        // 4. Gerar senha se não fornecida
        if (!$newPassword) {
            $newPassword = $this->generateSecurePassword();
            $this->info("🔑 Senha gerada automaticamente");
        }

        // 5. Confirmar ação
        if (!$this->confirm("Deseja realmente redefinir a senha?")) {
            $this->info("Operação cancelada.");
            return 0;
        }

        // 6. Atualizar senha no banco do tenant
        $user->password = Hash::make($newPassword);
        $user->save();

        // 7. Atualizar senha na tabela tenants do banco principal
        $tenant->update([
            'admin_password' => $newPassword,
        ]);

        $this->info("✅ Senha redefinida com sucesso!");
        $this->newLine();
        $this->line("📋 Credenciais de acesso:");
        $this->line("   Email: {$user->email}");
        $this->line("   Senha: {$newPassword}");
        $this->newLine();
        $this->info("💡 Agora você pode fazer login em:");
        $loginUrl = $tenant->admin_login_url ?? url("/customer/{$subdomain}/login");
        $this->line("   {$loginUrl}");

        return 0;
    }

    /**
     * Gera uma senha segura com mínimo 10 caracteres, incluindo letras, números e símbolos.
     */
    private function generateSecurePassword(int $length = 12): string
    {
        // Garantir mínimo de 10 caracteres
        $length = max(10, $length);
        
        // Caracteres permitidos
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        // Garantir pelo menos um de cada tipo
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Preencher o restante com caracteres aleatórios
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Embaralhar a senha
        return str_shuffle($password);
    }
}


