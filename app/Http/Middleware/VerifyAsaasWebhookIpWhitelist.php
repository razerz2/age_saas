<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyAsaasWebhookIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se whitelist está habilitada
        if (tenant_setting('finance.webhook_ip_whitelist_enabled') !== 'true') {
            return $next($request);
        }

        // Obter IPs permitidos
        $whitelistJson = tenant_setting('finance.webhook_ip_whitelist', '[]');
        $whitelist = json_decode($whitelistJson, true) ?? [];

        if (empty($whitelist)) {
            // Se whitelist está habilitada mas vazia, rejeitar
            Log::warning('Webhook Asaas rejeitado: whitelist habilitada mas vazia', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'IP não autorizado'], 403);
        }

        $clientIp = $request->ip();
        $isAllowed = in_array($clientIp, $whitelist);

        if (!$isAllowed) {
            Log::warning('Webhook Asaas rejeitado: IP não está na whitelist', [
                'tenant' => tenant()->subdomain ?? 'unknown',
                'ip' => $clientIp,
                'whitelist' => $whitelist,
            ]);
            return response()->json(['error' => 'IP não autorizado'], 403);
        }

        return $next($request);
    }
}

