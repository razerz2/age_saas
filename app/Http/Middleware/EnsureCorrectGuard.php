<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;

class EnsureCorrectGuard
{
    public function handle(Request $request, Closure $next)
    {
        $isTenantLogin = $request->segment(1) === 't';
        $isTenantArea  = $request->segment(1) === 'tenant';
        $isPatientPortal = $request->segment(1) === 'paciente';

        /**
         * LOGIN TENANT
         * /t/{tenant}/login
         */
        if ($isTenantLogin) {

            // Impede conflito com guard web
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }

            Auth::shouldUse('tenant');

            return $next($request);
        }

        /**
         * PORTAL DO PACIENTE
         * /paciente/...
         */
        if ($isPatientPortal) {

            // Impede conflito com outros guards
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }
            if (Auth::guard('tenant')->check()) {
                Auth::guard('tenant')->logout();
            }

            Auth::shouldUse('patient');

            return $next($request);
        }

        /**
         * ÁREA AUTENTICADA
         * /tenant/...
         */
        if ($isTenantArea) {

            if (!Tenant::current()) {
                return redirect('/')->withErrors(['tenant' => 'Tenant não carregado.']);
            }

            Auth::shouldUse('tenant');

            return $next($request);
        }

        /**
         * ROTAS DA PLATAFORMA
         */
        Auth::shouldUse('web');

        return $next($request);
    }
}
