<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Administrador',
                'email' => 'admin@plataforma.com',
                'email_verified_at' => null,
                'password' => '$2y$12$isU0mLjPSDo3cLy/w.duRe5ajXiCSDY.iKQzK3wDug2LMPQxiH4D.', // já hasheada
                'remember_token' => 'YK4bqd4QRTG6gA12bV8zvY5uCncTYmDsP4jUoRd4WawrMws3wSGKQQ6iqMtI',
                'created_at' => '2025-09-28 22:10:38',
                'updated_at' => '2025-09-28 22:10:38',
            ],
        ]);

         DB::table('tenants')->insert([
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
        ]);
    }
}
