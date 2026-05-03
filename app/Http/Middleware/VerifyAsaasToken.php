<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyAsaasToken
{
    public function handle(Request $request, Closure $next)
    {
        $provided = (string) $request->header('asaas-access-token', '');
        $asaas = function_exists('asaas_config') ? asaas_config() : [];
        $expected = (string) ($asaas['webhook_secret'] ?? config('services.asaas.webhook_secret', env('ASAAS_WEBHOOK_SECRET')));

        if (!$expected) {
            Log::warning('ASAAS_WEBHOOK_SECRET nao configurado.');
            return response()->json(['error' => 'Webhook token not configured'], 500);
        }

        if ($provided === '' || !hash_equals($expected, $provided)) {
            Log::warning('Token Asaas invalido.', [
                'has_header' => $provided !== '',
                'provided_first4' => $provided !== '' ? substr($provided, 0, 4) . '***' : null,
                'provided_length' => strlen($provided),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
