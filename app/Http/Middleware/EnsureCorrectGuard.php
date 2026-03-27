<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCorrectGuard
{
    public function handle(Request $request, Closure $next)
    {
        $segment1 = strtolower((string) $request->segment(1));
        $isCustomerLogin = $segment1 === 'customer';
        $isWorkspaceArea = $segment1 === 'workspace';
        $isPatientPortal = $segment1 === 'paciente';

        if ($isCustomerLogin) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }

            Auth::shouldUse('tenant');

            return $next($request);
        }

        if ($isPatientPortal || ($isWorkspaceArea && $request->segment(2) === 'paciente')) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }
            if (Auth::guard('tenant')->check()) {
                Auth::guard('tenant')->logout();
            }

            Auth::shouldUse('patient');

            return $next($request);
        }

        if ($isWorkspaceArea) {
            $currentTenant = Tenant::current();

            if (! $currentTenant) {
                $slug = $request->route('slug');
                if (! is_string($slug) || $slug === '') {
                    $slug = $request->segment(2);
                }

                if (is_string($slug) && $slug !== '') {
                    $tenantFromSlug = Tenant::where('subdomain', $slug)->first();
                    if ($tenantFromSlug) {
                        $tenantFromSlug->makeCurrent();
                        $currentTenant = $tenantFromSlug;

                        if ($request->hasSession()) {
                            $request->session()->put('tenant_slug', $tenantFromSlug->subdomain);
                        }
                    }
                }
            }

            if (! $currentTenant) {
                return redirect('/')->withErrors(['tenant' => 'Tenant nao carregado.']);
            }

            Auth::shouldUse('tenant');

            return $next($request);
        }

        Auth::shouldUse('web');

        return $next($request);
    }
}
