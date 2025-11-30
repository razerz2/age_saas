<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantModulePermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        $user = Auth::guard('tenant')->user();

        if (!$user) {
            abort(403, 'Acesso negado.');
        }

        // Admin tem acesso a todos os módulos
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Verifica se o usuário tem acesso ao módulo
        if (!in_array($moduleName, $user->modules ?? [])) {
            abort(403, 'Você não tem permissão para acessar este módulo.');
        }

        return $next($request);
    }
}
