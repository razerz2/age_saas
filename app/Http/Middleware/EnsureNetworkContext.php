<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Garante que uma rede de clínicas foi detectada
 * Alias: ensure.network.context
 */
class EnsureNetworkContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->bound('currentNetwork')) {
            abort(404, 'Rede de clínicas não encontrada');
        }

        return $next($request);
    }
}

