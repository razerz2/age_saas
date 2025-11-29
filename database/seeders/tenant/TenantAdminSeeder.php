<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TenantAdminSeeder extends Seeder
{
    public function run()
    {
        // ⬅️ Pegamos o tenant_id real enviado pelo TenantProvisioner
        $tenantId = config('tenant.current_id');
        $subdomain = config('tenant.current_subdomain', 'tenant');

        if (!$tenantId) {
            Log::error("❌ TenantAdminSeeder: tenant_id ausente!");
            return;
        }

        // limpa subdomínio para formato válido de domínio
        $domain = $this->sanitizeDomain($subdomain);

        // gera email dinâmico
        $email = $this->generateAdminEmail($domain);

        // Usar explicitamente a conexão 'tenant' para garantir que está usando o banco correto
        DB::connection('tenant')->table('users')->insert([
            'tenant_id'  => $tenantId, // <-- obrigatório
            'name'       => 'Administrador',
            'name_full'  => 'Administrador do Sistema',
            'telefone'   => '00000000000',
            'email'      => $email,
            'password'   => Hash::make('admin123'),
            'is_doctor'  => false,
            'status'     => 'active',
            'modules'    => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        Log::info("✅ Usuário admin criado", [
            'email' => $email,
            'tenant_id' => $tenantId
        ]);
    }

    private function sanitizeDomain(string $subdomain): string
    {
        $slug = Str::slug($subdomain);
        $clean = preg_replace('/[^a-z0-9\-]/', '', $slug);
        return !empty($clean) ? $clean : 'tenant';
    }

    private function generateAdminEmail(string $domain): string
    {
        return "admin@{$domain}.com";
    }
}
