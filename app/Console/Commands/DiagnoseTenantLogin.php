<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiagnoseTenantLogin extends Command
{
    protected $signature = 'tenant:diagnose {subdomain}';
    protected $description = 'Diagnostica problemas de login para um tenant específico';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        
        $this->info("🔍 Diagnosticando tenant: {$subdomain}");
        $this->newLine();

        // 1. Verificar se o tenant existe na plataforma
        $this->info("1️⃣ Verificando tenant na plataforma...");
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        
        if (!$tenant) {
            $this->error("❌ Tenant '{$subdomain}' não encontrado na tabela tenants!");
            return 1;
        }

        $this->info("✅ Tenant encontrado:");
        $this->line("   ID: {$tenant->id}");
        $this->line("   Nome: {$tenant->trade_name}");
        $this->line("   Subdomain: {$tenant->subdomain}");
        $this->line("   DB Name: {$tenant->db_name}");
        $this->line("   DB Host: {$tenant->db_host}");
        $this->line("   DB User: {$tenant->db_username}");
        $this->line("   Status: {$tenant->status}");
        $this->newLine();

        // 2. Ativar o tenant
        $this->info("2️⃣ Ativando tenant...");
        try {
            $tenant->makeCurrent();
            $this->info("✅ Tenant ativado com sucesso");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao ativar tenant: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // 3. Verificar conexão com o banco
        $this->info("3️⃣ Verificando conexão com banco de dados...");
        try {
            DB::connection('tenant')->getPdo();
            $this->info("✅ Conexão com banco OK");
            $this->line("   Database: " . config('database.connections.tenant.database'));
            $this->line("   Host: " . config('database.connections.tenant.host'));
        } catch (\Exception $e) {
            $this->error("❌ Erro ao conectar no banco: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // 4. Verificar se a tabela users existe
        $this->info("4️⃣ Verificando tabela users...");
        try {
            $tableExists = DB::connection('tenant')->selectOne(
                "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users')"
            );
            
            if (!$tableExists || !$tableExists->exists) {
                $this->error("❌ Tabela 'users' não existe no banco do tenant!");
                $this->warn("💡 Execute: php artisan tenants:migrate --tenants={$tenant->id}");
                return 1;
            }
            $this->info("✅ Tabela 'users' existe");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao verificar tabela: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // 5. Listar usuários
        $this->info("5️⃣ Listando usuários no banco do tenant...");
        try {
            $users = User::on('tenant')->get();
            
            if ($users->isEmpty()) {
                $this->warn("⚠️ Nenhum usuário encontrado no banco do tenant!");
                $this->newLine();
                $this->info("💡 Para criar um usuário admin, execute:");
                $this->line("   php artisan tenant:create-admin {$tenant->id}");
            } else {
                $this->info("✅ Encontrados {$users->count()} usuário(s):");
                $this->newLine();
                
                $headers = ['ID', 'Nome', 'Email', 'Status', 'Role', 'Is Doctor'];
                $rows = [];
                
                foreach ($users as $user) {
                    $rows[] = [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->status ?? 'N/A',
                        $user->role ?? 'N/A',
                        $user->is_doctor ? 'Sim' : 'Não',
                    ];
                }
                
                $this->table($headers, $rows);
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro ao listar usuários: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // 6. Verificar email do admin esperado
        $this->info("6️⃣ Verificando email do admin esperado...");
        $domain = preg_replace('/[^a-z0-9\-]/', '', \Illuminate\Support\Str::slug($tenant->subdomain));
        $expectedEmail = "admin@{$domain}.com";
        $this->line("   Email esperado: {$expectedEmail}");
        
        $adminUser = User::on('tenant')->where('email', $expectedEmail)->first();
        if ($adminUser) {
            $this->info("✅ Usuário admin encontrado!");
            $this->line("   ID: {$adminUser->id}");
            $this->line("   Nome: {$adminUser->name}");
            $this->line("   Status: {$adminUser->status}");
            $this->line("   Senha padrão: admin123");
        } else {
            $this->warn("⚠️ Usuário admin não encontrado com email: {$expectedEmail}");
        }
        $this->newLine();

        $this->info("✅ Diagnóstico concluído!");
        return 0;
    }
}










