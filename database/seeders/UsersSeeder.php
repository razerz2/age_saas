<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // ajuste o namespace conforme o seu modelo real
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 🔐 Senha padrão em texto simples (para lembrar facilmente)
        $plainPassword = '10203040';

        User::updateOrCreate(
            ['email' => 'admin@plataforma.com'],
            [
                'id' => 1,
                'name' => 'Administrador',
                'email_verified_at' => now(),
                'password' => Hash::make($plainPassword),
                'remember_token' => Str::random(60),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Apenas para feedback no terminal
        $this->command->info("✅ Usuário administrador criado/atualizado com sucesso!");
        $this->command->info("📧 Email: admin@plataforma.com");
        $this->command->info("🔑 Senha: {$plainPassword}");
    }
}
