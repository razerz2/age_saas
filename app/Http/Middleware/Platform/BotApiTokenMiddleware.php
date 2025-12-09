<?php

namespace App\Http\Middleware\Platform;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\ApiTenantToken;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BotApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'error' => 'Token ausente'
            ], 401);
        }

        $plainToken = substr($authHeader, 7);
        $hash = hash('sha256', $plainToken);

        $tokenRecord = ApiTenantToken::with('tenant')
            ->where('token_hash', $hash)
            ->where('active', true)
            ->first();

        if (!$tokenRecord || !$tokenRecord->tenant) {
            Log::warning('Token de API inválido', [
                'token_hash_prefix' => substr($hash, 0, 8) . '***',
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Token inválido'
            ], 401);
        }

        if ($tokenRecord->expires_at && now()->greaterThan($tokenRecord->expires_at)) {
            Log::warning('Token de API expirado', [
                'token_id' => $tokenRecord->id,
                'expires_at' => $tokenRecord->expires_at,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Token expirado'
            ], 401);
        }

        // Atualizar último uso
        $tokenRecord->update([
            'last_used_at' => now(),
            'last_ip' => $request->ip(),
        ]);

        // Disponibiliza o tenant e o token no request
        $request->attributes->set('bot_api_tenant', $tokenRecord->tenant);
        $request->attributes->set('bot_api_token', $tokenRecord);

        Log::info('Token de API validado', [
            'token_id' => $tokenRecord->id,
            'tenant_id' => $tokenRecord->tenant->id,
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
