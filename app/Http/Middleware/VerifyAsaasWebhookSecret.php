<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyAsaasWebhookSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se módulo está habilitado
        if (tenant_setting('finance.enabled') !== 'true') {
            Log::warning('Webhook Asaas rejeitado: módulo financeiro desabilitado', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Módulo financeiro desabilitado'], 403);
        }

        // Obter secret configurado
        $webhookSecret = tenant_setting('finance.asaas.webhook_secret');
        $providedSecret = $request->header('X-ASAAS-WEBHOOK-SECRET');

        // Validar secret usando hash_equals para comparação segura
        if (empty($webhookSecret) || empty($providedSecret)) {
            Log::warning('Webhook Asaas rejeitado: secret não configurado ou não fornecido', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Secret inválido'], 401);
        }

        if (!hash_equals($webhookSecret, $providedSecret)) {
            Log::warning('Webhook Asaas rejeitado: secret inválido', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
                'secret_provided_length' => strlen($providedSecret),
            ]);
            return response()->json(['error' => 'Secret inválido'], 401);
        }

        return $next($request);
    }
}

