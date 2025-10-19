<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;

class TenantsSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'id' => '45f00cc3-58bf-43e7-a8b8-59ada9815c93',
                'legal_name' => 'Odonto Vida LTDA',
                'trade_name' => 'Clínica Odonto Vida',
                'document' => '32.456.789/0001-55',
                'email' => 'contato@odontovida.com.br',
                'phone' => '(11) 3456-7890',
                'subdomain' => 'odontovida',
                'db_host' => '127.0.0.1',
                'db_port' => '5432',
                'db_name' => 'odontovida_db',
                'db_username' => 'odontovida_user',
                'db_password' => 'senhaSegura123',
                'status' => 'trial',
                'trial_ends_at' => '2025-11-30',
                'created_at' => '2025-09-29 02:58:14',
                'updated_at' => '2025-09-29 02:58:14',
            ],
            [
                'id' => '6b04d3e4-fd65-411a-abac-205d0395f16f',
                'legal_name' => 'Cuidar Bem Saúde Infantil LTDA',
                'trade_name' => 'Cuidar Bem Pediatria',
                'document' => '12.987.654/0001-22',
                'email' => 'atendimento@cuidarbem.com.br',
                'phone' => '(21) 98765-4321',
                'subdomain' => 'cuidar-bem',
                'db_host' => 'localhost',
                'db_port' => '5432',
                'db_name' => 'cuidarbem_db',
                'db_username' => 'cuidarbem_user',
                'db_password' => 'pediatria@2025',
                'status' => 'trial',
                'trial_ends_at' => '2025-12-15',
                'created_at' => '2025-10-04 11:43:24',
                'updated_at' => '2025-10-04 11:43:24',
            ],
            [
                'id' => '4f4a05df-c32f-4863-abbb-4fbfcfe989f2',
                'legal_name' => 'Derma+ Saúde Integrada EIRELI',
                'trade_name' => 'Derma+',
                'document' => '45.123.678/0001-77',
                'email' => 'contato@dermaplus.com.br',
                'phone' => '(31) 2222-3344',
                'subdomain' => 'dermaplus',
                'db_host' => 'localhost',
                'db_port' => '5432',
                'db_name' => 'dermaplus_db',
                'db_username' => 'dermaplus_user',
                'db_password' => 'dermaSecure!2025',
                'status' => 'active',
                'trial_ends_at' => null,
                'created_at' => '2025-10-04 11:47:37',
                'updated_at' => '2025-10-04 11:47:37',
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::updateOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }

        $this->command->info('🏢 Tenants padrão inseridos com sucesso!');
    }
}