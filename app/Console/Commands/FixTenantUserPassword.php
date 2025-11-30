<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FixTenantUserPassword extends Command
{
    protected $signature = 'tenant:fix-password 
                            {tenant : O subdomain do tenant}
                            {email : O email do usuário}
                            {--password= : Nova senha (opcional, se não informado será solicitado)}
                            {--check : Apenas verifica o formato da senha sem alterar}';

    protected $description = 'Corrige ou redefine a senha de um usuário do tenant';

    public function handle()
    {
        $tenantSlug = $this->argument('tenant');
        $email = $this->argument('email');

        // Busca o tenant
        $tenant = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantSlug}' não encontrado.");
            return Command::FAILURE;
        }

        $this->info("✅ Tenant encontrado: {$tenant->name}");

        // Ativa o tenant
        $tenant->makeCurrent();

        // Busca o usuário
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Usuário com email '{$email}' não encontrado neste tenant.");
            return Command::FAILURE;
        }

        $this->info("✅ Usuário encontrado: {$user->name} ({$user->email})");

        // Verifica o formato da senha atual
        $password = $user->password;
        $isBcrypt = str_starts_with($password, '$2y$') || 
                    str_starts_with($password, '$2a$') || 
                    str_starts_with($password, '$2b$');

        $this->line("\n📋 Status atual da senha:");
        $this->line("   Formato: " . ($isBcrypt ? "✅ Bcrypt válido" : "❌ Formato inválido"));
        $this->line("   Tamanho: " . strlen($password) . " caracteres");
        $this->line("   Prefixo: " . substr($password, 0, 7));

        if ($this->option('check')) {
            $this->info("\n✅ Verificação concluída. Use sem --check para corrigir.");
            return Command::SUCCESS;
        }

        // Se a senha já está correta e não foi solicitada nova senha, apenas informa
        if ($isBcrypt && !$this->option('password')) {
            $this->info("\n✅ A senha já está no formato correto (Bcrypt).");
            $this->line("💡 Se deseja redefinir a senha, use a opção --password= ou informe quando solicitado.");
            return Command::SUCCESS;
        }

        // Se a senha está incorreta ou foi solicitada nova senha
        if (!$isBcrypt || $this->option('password')) {
            // Solicita nova senha se não foi informada
            $newPassword = $this->option('password');
            
            if (!$newPassword) {
                $newPassword = $this->secret('Digite a nova senha:');
                $confirmPassword = $this->secret('Confirme a nova senha:');
                
                if ($newPassword !== $confirmPassword) {
                    $this->error("❌ As senhas não coincidem.");
                    return Command::FAILURE;
                }
                
                if (empty($newPassword)) {
                    $this->error("❌ A senha não pode estar vazia.");
                    return Command::FAILURE;
                }
            }

            // Atualiza a senha usando o mutator (que agora está correto)
            $user->password = $newPassword;
            $user->save();

            $this->info("\n✅ Senha atualizada com sucesso!");
            $this->line("   Email: {$user->email}");
            $this->line("   Nova senha: " . ($this->option('password') ? '***' : 'definida'));
            
            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }
}

