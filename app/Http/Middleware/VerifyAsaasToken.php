<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyAsaasToken
{
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->header('asaas-access-token');
        $asaas = function_exists('asaas_config') ? asaas_config() : [];
        $expected = (string) ($asaas['webhook_secret'] ?? config('services.asaas.webhook_secret', env('ASAAS_WEBHOOK_SECRET')));

        Log::info('🔐 Verificando token Asaas', [
            'has_header' => $provided !== null,
            'provided_first4' => $provided ? substr($provided, 0, 4) . '***' : null,
            'expected_first4' => $expected ? substr($expected, 0, 4) . '***' : null,
        ]);

        if (!$expected) {
            Log::warning('⚠️ ASAAS_WEBHOOK_SECRET não configurado.');
            return response()->json(['error' => 'Webhook token not configured'], 500);
        }

        if ($provided !== $expected) {
            Log::error('🚫 Token Asaas inválido!', [
                'provided' => $provided,
                'expected_first4' => substr($expected, 0, 4) . '***',
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('✅ Token Asaas validado com sucesso.');
        return $next($request);
    }
}
