<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfTenantUnauthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('tenant');

        if ($guard->check()) {
            $this->syncTenantSlugInSession($request);

            return $next($request);
        }

        $slug = $this->getTenantSlug($request);

        if (! $slug) {
            abort(403, 'Acesso negado. Faca login pelo link da sua clinica.');
        }

        if ($request->hasSession()) {
            $request->session()->put('tenant_slug', $slug);
        }

        return redirect()->route('tenant.login', ['slug' => $slug]);
    }

    protected function syncTenantSlugInSession(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $currentTenant = Tenant::current();
        if ($currentTenant && is_string($currentTenant->subdomain) && $currentTenant->subdomain !== '') {
            $request->session()->put('tenant_slug', $currentTenant->subdomain);

            return;
        }

        $routeSlug = $request->route('slug');
        if (is_string($routeSlug) && $routeSlug !== '') {
            $request->session()->put('tenant_slug', $routeSlug);
        }
    }

    protected function getTenantSlug(Request $request): ?string
    {
        $routeSlug = $request->route('slug');
        if (is_string($routeSlug) && $routeSlug !== '') {
            return $routeSlug;
        }

        $legacyTenant = $request->route('tenant');
        if (is_string($legacyTenant) && $legacyTenant !== '') {
            return $legacyTenant;
        }

        if ($request->hasSession()) {
            $sessionSlug = $request->session()->get('tenant_slug');
            if (is_string($sessionSlug) && $sessionSlug !== '') {
                return $sessionSlug;
            }
        }

        $currentTenant = Tenant::current();
        if ($currentTenant && is_string($currentTenant->subdomain) && $currentTenant->subdomain !== '') {
            return $currentTenant->subdomain;
        }

        return null;
    }
}
