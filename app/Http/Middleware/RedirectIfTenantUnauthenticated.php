<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;

class RedirectIfTenantUnauthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('tenant')->check()) {
            return $next($request);
        }

        $slug =
            $request->route('tenant') ??
            session('tenant_slug') ??
            Tenant::current()?->subdomain;

        if (!$slug) {
            return redirect('/platform/login');
        }

        return redirect()->route('tenant.login', [
            'tenant' => $slug
        ]);
    }
}
