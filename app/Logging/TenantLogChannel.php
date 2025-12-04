<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use Spatie\Multitenancy\Models\Tenant;

class TenantLogChannel
{
    public function __invoke(array $config)
    {
        $logger = new Logger('tenant-dynamic');
        
        // Respeita o nível de log configurado no ambiente
        $level = $this->parseLogLevel($config['level'] ?? env('LOG_LEVEL', 'debug'));

        $handler = new PerTenantStreamHandler($level);
        
        // Configura o formatter padrão do Laravel para consistência
        $formatter = new LineFormatter(
            format: "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            dateFormat: 'Y-m-d H:i:s',
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: false
        );
        $handler->setFormatter($formatter);
        
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Converte string de nível para constante do Logger
     */
    protected function parseLogLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
            default => Logger::DEBUG,
        };
    }
}

class PerTenantStreamHandler extends AbstractProcessingHandler
{
    public function __construct(int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * Escreve a linha formatada no arquivo correto (compatível com Monolog 3)
     */
    protected function write(LogRecord $record): void
    {
        $tenantName = $this->resolveTenantName();
        $logPath = $this->getTenantLogPath($tenantName);

        $dir = \dirname($logPath);
        if (!\is_dir($dir)) {
            if (!@\mkdir($dir, 0775, true) && !\is_dir($dir)) {
                // Se falhar ao criar diretório, tenta logar no log padrão
                error_log("Erro ao criar diretório de log do tenant: {$dir}");
                return;
            }
        }

        $line = (string) $record->formatted;
        if (@\file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX) === false) {
            // Se falhar ao escrever, tenta logar no log padrão
            error_log("Erro ao escrever log do tenant: {$logPath}");
        }
    }

    /**
     * Resolve o nome do tenant atual
     */
    protected function resolveTenantName(): string
    {
        // 1️⃣ Prioridade: Tenant ativo pelo Spatie
        if (\class_exists(Tenant::class)) {
            $current = Tenant::current();
            if ($current) {
                return $current->subdomain ?? (string) ($current->id ?? 'unknown');
            }
        }

        // 2️⃣ Tenant registrado no container manualmente
        $tenant = \app()->bound('currentTenant') ? \app('currentTenant') : null;
        if ($tenant) {
            return $tenant->subdomain ?? (string) ($tenant->id ?? 'unknown');
        }

        // 3️⃣ Sem tenant → plataforma
        return 'platform';
    }

    /**
     * Define o caminho de log por tenant
     */
    protected function getTenantLogPath(string $tenantName): string
    {
        if ($tenantName === 'platform') {
            return \storage_path('logs/laravel.log');
        }

        // (opcional) use rotação diária se quiser:
        // $date = date('Y-m-d');
        // return storage_path("logs/tenants/{$tenantName}/{$date}.log");

        return \storage_path("logs/tenants/{$tenantName}/laravel.log");
    }
}
