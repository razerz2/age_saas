<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Verifica se a tabela existe antes de tentar inserir dados
        if (!Schema::hasTable('users')) {
            $this->command->error("❌ A tabela 'users' não existe no banco de dados.");
            $this->command->warn("💡 Execute primeiro as migrations: php artisan migrate");
            return;
        }

        // 🔐 Senha padrão
        $plainPassword = '10203040';

        // 📦 Módulos padrão (array real)
        $defaultModules = [
            "tenants",
            "pre_tenants",
            "plans",
            "subscriptions",
            "invoices",
            "medical_specialties_catalog",
            "notifications_outbox",
            "system_notifications",
            "locations",
            "users",
            "settings"
        ];

        User::updateOrCreate(
            ['email' => 'admin@plataforma.com'],
            [
                'name' => 'Administrador',
                'name_full' => 'Administrador',
                'email_verified_at' => now(),
                'password' => Hash::make($plainPassword),
                'modules' => $defaultModules, // ✅ salva como array
                'remember_token' => Str::random(60),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 💬 Feedback no terminal
        $this->command->info("✅ Usuário administrador criado/atualizado com sucesso!");
        $this->command->info("📧 Email: admin@plataforma.com");
        $this->command->info("🔑 Senha: {$plainPassword}");
        $this->command->info("📦 Módulos: " . implode(', ', $defaultModules));
    }
}