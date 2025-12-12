<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestTenantLogin extends Command
{
    protected $signature = 'tenant:test-login {subdomain} {email} {password}';
    protected $description = 'Testa o login de um usuário em um tenant';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("🔍 Testando login para tenant: {$subdomain}");
        $this->info("   Email: {$email}");
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

        // 3. Buscar usuário
        $user = User::on('tenant')->where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuário não encontrado!");
            return 1;
        }

        $this->info("✅ Usuário encontrado:");
        $this->line("   ID: {$user->id}");
        $this->line("   Nome: {$user->name}");
        $this->line("   Status: {$user->status}");
        $this->line("   Role: {$user->role}");
        $this->newLine();

        // 4. Verificar senha
        $this->info("🔐 Verificando senha...");
        $passwordHash = $user->password;
        $this->line("   Hash armazenado: " . substr($passwordHash, 0, 20) . "...");
        
        $passwordCheck = Hash::check($password, $passwordHash);
        
        if ($passwordCheck) {
            $this->info("✅ Senha está CORRETA!");
        } else {
            $this->error("❌ Senha está INCORRETA!");
            $this->newLine();
            $this->warn("💡 Tentando verificar com diferentes variações...");
            
            // Testar variações comuns
            $variations = [
                'admin123',
                'Admin123',
                'ADMIN123',
                '10203040',
                'password',
            ];
            
            foreach ($variations as $variation) {
                if (Hash::check($variation, $passwordHash)) {
                    $this->info("✅ Senha correta encontrada: '{$variation}'");
                    break;
                }
            }
        }
        $this->newLine();

        // 5. Tentar autenticação completa
        $this->info("🔐 Testando autenticação completa...");
        Auth::shouldUse('tenant');
        
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];
        
        $attempt = Auth::guard('tenant')->attempt($credentials);
        
        if ($attempt) {
            $this->info("✅ Autenticação bem-sucedida!");
            $this->line("   Usuário autenticado: " . Auth::guard('tenant')->user()->name);
            Auth::guard('tenant')->logout();
        } else {
            $this->error("❌ Autenticação falhou!");
            $this->warn("   Verifique se a senha está correta.");
        }
        $this->newLine();

        return 0;
    }
}















