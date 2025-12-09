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
        $segment1 = $request->segment(1);
        $isCustomerLogin = $segment1 === 'customer';
        $isWorkspaceArea  = $segment1 === 'workspace';
        $isPatientPortal = $segment1 === 'paciente';

        /**
         * LOGIN TENANT (ÁREA PÚBLICA)
         * /customer/{slug}/login
         */
        if ($isCustomerLogin) {

            // Impede conflito com guard web
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }

            Auth::shouldUse('tenant');

            return $next($request);
        }

        /**
         * PORTAL DO PACIENTE
         * /workspace/{slug}/paciente/... ou /paciente/...
         */
        if ($isPatientPortal || ($isWorkspaceArea && $request->segment(2) === 'paciente')) {

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
         * /workspace/{slug}/...
         */
        if ($isWorkspaceArea) {

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
