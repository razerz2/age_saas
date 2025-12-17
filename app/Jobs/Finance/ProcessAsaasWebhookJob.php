<?php

namespace App\Jobs\Finance;

use App\Services\Finance\Reconciliation\AsaasWebhookProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAsaasWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onQueue('finance');
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasWebhookProcessor $processor): void
    {
        // Verificação obrigatória
        if (tenant_setting('finance.enabled') !== 'true') {
            Log::info('Job de webhook ignorado: módulo financeiro desabilitado', [
                'tenant' => tenant()->subdomain ?? 'unknown',
            ]);
            return;
        }

        try {
            $result = $processor->handle($this->payload);

            Log::info('Webhook processado via job', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook via job', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw para retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de webhook falhou definitivamente', [
            'tenant' => tenant()->subdomain ?? 'unknown',
            'error' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);
    }
}

