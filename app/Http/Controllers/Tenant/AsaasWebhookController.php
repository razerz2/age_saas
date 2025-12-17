<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Finance\ProcessAsaasWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController extends Controller
{
    /**
     * Processa webhook do Asaas
     * POST /{tenant}/webhooks/asaas
     * 
     * Validações já feitas pelo middleware:
     * - Rate limit
     * - Secret validation
     * - IP whitelist (se habilitado)
     */
    public function handle(Request $request)
    {
        // Verificação final (redundante mas segura)
        if (tenant_setting('finance.enabled') !== 'true') {
            Log::warning('Webhook Asaas rejeitado: módulo financeiro desabilitado', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Módulo financeiro desabilitado'], 403);
        }

        // Verificar feature flag
        if (tenant_setting('finance.webhook_enabled') === 'false') {
            Log::info('Webhook Asaas ignorado: feature flag desabilitada', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Webhook desabilitado'], 200);
        }

        $payload = $request->all();
        $eventId = $payload['event'] ?? $payload['id'] ?? $payload['payment']['id'] ?? 'UNKNOWN';

        // Log estruturado
        Log::channel('finance')->info('Webhook Asaas recebido', [
            'tenant' => tenant()->subdomain ?? 'unknown',
            'event_id' => $eventId,
            'event_type' => $payload['event'] ?? 'UNKNOWN',
            'ip' => $request->ip(),
            'payment_id' => $payload['payment']['id'] ?? null,
        ]);

        // Despachar job para processar webhook
        ProcessAsaasWebhookJob::dispatch($payload);

        return response()->json(['message' => 'Webhook recebido e será processado'], 200);
    }
}

