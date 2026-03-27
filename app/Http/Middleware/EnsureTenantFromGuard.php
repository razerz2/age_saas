<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantFromGuard
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('tenant');

        if ($guard->check()) {
            $user = $guard->user();

            if ($user && $user->tenant) {
                $tenant = $user->tenant;
                $currentTenant = Tenant::current();

                if (! $currentTenant || (string) $currentTenant->id !== (string) $tenant->id) {
                    $tenant->makeCurrent();
                }

                if ($request->hasSession()) {
                    $request->session()->put('tenant_slug', $tenant->subdomain);
                }
            }

            return $next($request);
        }

        $currentTenant = Tenant::current();
        if ($currentTenant && $request->hasSession() && ! $request->session()->has('tenant_slug')) {
            $request->session()->put('tenant_slug', $currentTenant->subdomain);
        }

        return $next($request);
    }
}
