<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;

class TenantLogChannel
{
    public function __invoke(array $config)
    {
        $logger = new Logger('tenant-dynamic');
        $level = Logger::DEBUG;

        $logger->pushHandler(new PerTenantStreamHandler($level));

        return $logger;
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
            @\mkdir($dir, 0775, true);
        }

        $line = (string) $record->formatted;
        @\file_put_contents($logPath, $line, FILE_APPEND);
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
