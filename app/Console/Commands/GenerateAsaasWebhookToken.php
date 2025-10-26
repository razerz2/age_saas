<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateAsaasWebhookToken extends Command
{
    /**
     * Nome do comando (executa com php artisan asaas:generate-token)
     */
    protected $signature = 'asaas:generate-token';

    /**
     * Descrição
     */
    protected $description = 'Gera uma nova chave de autenticação para o webhook Asaas e atualiza o .env';

    public function handle()
    {
        try {
            // 🔐 Gera token seguro (32 caracteres hexadecimais)
            $newToken = bin2hex(random_bytes(16));

            $envPath = base_path('.env');
            $envContent = File::get($envPath);

            // 🔍 Substitui se já existir ASAAS_WEBHOOK_SECRET, senão adiciona
            if (Str::contains($envContent, 'ASAAS_WEBHOOK_SECRET=')) {
                $envContent = preg_replace(
                    '/^ASAAS_WEBHOOK_SECRET=.*$/m',
                    "ASAAS_WEBHOOK_SECRET={$newToken}",
                    $envContent
                );
            } else {
                $envContent .= "\nASAAS_WEBHOOK_SECRET={$newToken}\n";
            }

            File::put($envPath, $envContent);

            Log::info('🔐 Novo token ASAAS_WEBHOOK_SECRET gerado com sucesso.', ['token' => $newToken]);

            $this->info('✅ Nova chave de autenticação gerada com sucesso!');
            $this->line("🔑 Token: <comment>{$newToken}</comment>");
            $this->line("\n➡️ Copie este token e atualize o campo 'Token de autenticação' no painel do Asaas.");

        } catch (\Throwable $e) {
            Log::error('💥 Erro ao gerar novo token Asaas', ['erro' => $e->getMessage()]);
            $this->error("Erro ao gerar token: {$e->getMessage()}");
        }
    }
}
