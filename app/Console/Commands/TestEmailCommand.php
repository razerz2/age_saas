<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {--to= : Endereço de email para enviar o teste (opcional)}';
    protected $description = 'Testa a configuração de email enviando um email de teste';

    public function handle()
    {
        $this->info('📧 Testando configuração de email...');
        $this->newLine();

        // Mostrar configurações atuais
        $this->info('📋 Configurações atuais:');
        $this->line('   Mailer: ' . Config::get('mail.default'));
        $this->line('   Host: ' . Config::get('mail.mailers.smtp.host'));
        $this->line('   Port: ' . Config::get('mail.mailers.smtp.port'));
        $this->line('   Encryption: ' . Config::get('mail.mailers.smtp.encryption'));
        $this->line('   Username: ' . Config::get('mail.mailers.smtp.username'));
        $this->line('   From Address: ' . Config::get('mail.from.address'));
        $this->line('   From Name: ' . Config::get('mail.from.name'));
        $this->newLine();

        // Determinar destinatário
        $to = $this->option('to') ?: Config::get('mail.from.address', 'teste@localhost');
        
        if (!$to || $to === 'teste@localhost') {
            $this->warn('⚠️  Nenhum destinatário especificado. Use --to=seuemail@exemplo.com');
            $this->warn('   Tentando enviar para: ' . $to);
        } else {
            $this->info('📬 Enviando email de teste para: ' . $to);
        }
        
        $this->newLine();

        // Tentar enviar email
        try {
            Mail::raw('Este é um email de teste do sistema de agendamento.', function ($message) use ($to) {
                $message->to($to)
                        ->subject('Teste de Email - Sistema de Agendamento');
            });

            $this->info('✅ Email enviado com sucesso!');
            $this->line('   Verifique a caixa de entrada (e spam) de: ' . $to);
            $this->newLine();
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao enviar email:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('💡 Dicas para resolver:');
            $this->line('   1. Verifique se as credenciais no .env estão corretas');
            $this->line('   2. Certifique-se de que MAIL_FROM_ADDRESS = MAIL_USERNAME');
            $this->line('   3. Verifique se a porta e criptografia estão corretas');
            $this->line('   4. Execute: php artisan config:clear');
            $this->line('   5. Verifique se não há espaços extras nas senhas do .env');
            $this->newLine();
            
            return 1;
        }
    }
}

