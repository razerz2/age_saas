<?php

namespace App\Services\Finance;

use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\DoctorCommission;
use App\Models\Tenant\AsaasWebhookEvent;
use App\Services\Finance\AsaasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceHealthCheckService
{
    /**
     * Executa todos os health checks
     */
    public function runAll(): array
    {
        return [
            'webhook' => $this->checkWebhook(),
            'queue' => $this->checkQueue(),
            'asaas_connectivity' => $this->checkAsaasConnectivity(),
            'pending_inconsistencies' => $this->checkPendingInconsistencies(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Verifica saúde dos webhooks
     */
    public function checkWebhook(): array
    {
        $last24h = Carbon::now()->subDay();
        
        $total = AsaasWebhookEvent::where('created_at', '>=', $last24h)->count();
        $success = AsaasWebhookEvent::where('created_at', '>=', $last24h)
            ->where('status', 'success')
            ->count();
        $errors = AsaasWebhookEvent::where('created_at', '>=', $last24h)
            ->where('status', 'error')
            ->count();
        $skipped = AsaasWebhookEvent::where('created_at', '>=', $last24h)
            ->where('status', 'skipped')
            ->count();

        $errorRate = $total > 0 ? ($errors / $total) * 100 : 0;
        $successRate = $total > 0 ? ($success / $total) * 100 : 0;

        $isHealthy = $errorRate < 10; // Menos de 10% de erro

        return [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'total' => $total,
            'success' => $success,
            'errors' => $errors,
            'skipped' => $skipped,
            'error_rate' => round($errorRate, 2),
            'success_rate' => round($successRate, 2),
            'threshold' => 10, // 10% de erro máximo
        ];
    }

    /**
     * Verifica saúde da fila
     */
    public function checkQueue(): array
    {
        try {
            // Verificar se há jobs pendentes na fila finance
            $pendingJobs = Queue::size('finance');
            
            // Verificar se há jobs falhados
            $failedJobs = DB::table('failed_jobs')
                ->where('queue', 'finance')
                ->where('failed_at', '>=', Carbon::now()->subDay())
                ->count();

            $isHealthy = $pendingJobs < 100 && $failedJobs < 10;

            return [
                'status' => $isHealthy ? 'healthy' : 'unhealthy',
                'pending_jobs' => $pendingJobs,
                'failed_jobs_24h' => $failedJobs,
                'threshold_pending' => 100,
                'threshold_failed' => 10,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica conectividade com Asaas
     */
    public function checkAsaasConnectivity(): array
    {
        try {
            $asaasService = new AsaasService();
            
            // Tentar buscar um pagamento de teste (ou fazer uma chamada simples)
            // Por enquanto, apenas verificar se a API key está configurada
            $apiKey = tenant_setting('finance.asaas.api_key');
            $environment = tenant_setting('finance.asaas.environment', 'sandbox');

            $isConfigured = !empty($apiKey);
            $isProduction = $environment === 'production';

            return [
                'status' => $isConfigured ? 'configured' : 'not_configured',
                'environment' => $environment,
                'api_key_set' => $isConfigured,
                'is_production' => $isProduction,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica inconsistências pendentes
     */
    public function checkPendingInconsistencies(): array
    {
        $issues = [];

        // 1. Cobranças pagas sem transação
        $paidChargesWithoutTransaction = FinancialCharge::where('status', 'paid')
            ->whereDoesntHave('transaction')
            ->count();

        if ($paidChargesWithoutTransaction > 0) {
            $issues[] = [
                'type' => 'paid_charge_without_transaction',
                'count' => $paidChargesWithoutTransaction,
                'severity' => 'high',
            ];
        }

        // 2. Transações de receita sem comissão (quando deveria ter)
        if (tenant_setting('finance.doctor_commission_enabled') === 'true') {
            $transactionsWithoutCommission = FinancialTransaction::where('type', 'income')
                ->where('status', 'paid')
                ->whereHas('appointment', function($q) {
                    $q->whereNotNull('doctor_id');
                })
                ->whereDoesntHave('commission')
                ->count();

            if ($transactionsWithoutCommission > 0) {
                $issues[] = [
                    'type' => 'transaction_without_commission',
                    'count' => $transactionsWithoutCommission,
                    'severity' => 'medium',
                ];
            }
        }

        // 3. Webhooks com erro não resolvidos
        $unresolvedErrors = AsaasWebhookEvent::where('status', 'error')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        if ($unresolvedErrors > 0) {
            $issues[] = [
                'type' => 'unresolved_webhook_errors',
                'count' => $unresolvedErrors,
                'severity' => 'high',
            ];
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'unhealthy',
            'issues' => $issues,
            'total_issues' => count($issues),
        ];
    }
}

