<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class TestSessionExpirationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:session-expiration {--clear-all : Limpa todas as sessões}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa sessões para testar redirecionamento quando sessão expira';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionPath = storage_path('framework/sessions');

        if (!$this->option('clear-all')) {
            $this->info('⚠️  Para limpar TODAS as sessões, use: php artisan test:session-expiration --clear-all');
            $this->newLine();
            $this->info('📋 Instruções para testar:');
            $this->line('1. Faça login na tenant');
            $this->line('2. No navegador, abra o DevTools (F12)');
            $this->line('3. Vá em Application → Cookies → http://127.0.0.1:8000');
            $this->line('4. Delete o cookie laravel_session');
            $this->line('5. Tente acessar /workspace/{tenant-slug}/dashboard');
            $this->line('6. Deve redirecionar para /t/{tenant-slug}/login');
            return 0;
        }

        if (!File::exists($sessionPath)) {
            $this->error("Diretório de sessões não encontrado: {$sessionPath}");
            return 1;
        }

        $files = File::files($sessionPath);
        $count = count($files);

        if ($count === 0) {
            $this->info('Nenhuma sessão encontrada para limpar.');
            return 0;
        }

        if (!$this->confirm("Tem certeza que deseja limpar {$count} sessões?")) {
            $this->info('Operação cancelada.');
            return 0;
        }

        foreach ($files as $file) {
            File::delete($file);
        }

        $this->info("✅ {$count} sessões foram limpas com sucesso!");
        $this->newLine();
        $this->info('📋 Próximos passos:');
        $this->line('1. Tente acessar qualquer rota protegida da tenant');
        $this->line('2. Deve redirecionar para /t/{tenant-slug}/login');
        $this->line('3. Verifique se NÃO redireciona para /login (plataforma)');

        return 0;
    }
}

