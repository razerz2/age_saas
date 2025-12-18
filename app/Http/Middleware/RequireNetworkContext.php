<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireNetworkContext
{
    /**
     * Handle an incoming request.
     *
     * Garante que uma rede de clínicas foi detectada.
     * Se não foi detectada, retorna 404.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->bound('currentNetwork')) {
            abort(404, 'Rede de clínicas não encontrada');
        }

        $network = app('currentNetwork');
        if (!$network->is_active) {
            abort(403, 'Esta rede de clínicas está inativa.');
        }

        return $next($request);
    }
}

