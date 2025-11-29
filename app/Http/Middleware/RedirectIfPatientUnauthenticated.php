<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfPatientUnauthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('patient')->check()) {
            return $next($request);
        }

        // Tenta obter o tenant da sessão ou da URL anterior
        $tenantSlug = session('tenant_slug') ?? $request->route('tenant');

        if ($tenantSlug) {
            return redirect()->route('patient.login', ['tenant' => $tenantSlug]);
        }

        // Se não conseguir identificar, redireciona para a página inicial
        return redirect('/')->withErrors(['auth' => 'Por favor, faça login para acessar o portal.']);
    }
}
