<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\User; // ajuste o namespace conforme o seu modelo real
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 🔐 Senha padrão
        $plainPassword = '10203040';

        // 📦 Módulos padrão (JSON codificado)
        $defaultModules = [
            "tenants",
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
                'email_verified_at' => now(),
                'password' => Hash::make($plainPassword),
                'modules' => json_encode($defaultModules, JSON_UNESCAPED_UNICODE), // ✅ novo campo
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
