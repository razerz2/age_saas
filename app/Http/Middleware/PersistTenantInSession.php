<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;

class PersistTenantInSession
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            $currentTenant = Tenant::current();

            if (! $currentTenant || (string) $currentTenant->id !== (string) $tenant->id) {
                $tenant->makeCurrent();
            }

            $this->setSessionSlug($request, $tenant->subdomain);
        }

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        $routeSlug = $this->extractSlugFromRequest($request);
        if ($routeSlug !== null) {
            $tenantFromRoute = Tenant::where('subdomain', $routeSlug)->first();
            if ($tenantFromRoute) {
                return $tenantFromRoute;
            }
        }

        $currentTenant = Tenant::current();
        if ($currentTenant instanceof Tenant) {
            return $currentTenant;
        }

        $sessionSlug = $this->getSessionSlug($request);
        if ($sessionSlug !== null) {
            $tenantFromSession = Tenant::where('subdomain', $sessionSlug)->first();
            if ($tenantFromSession) {
                return $tenantFromSession;
            }

            if ($request->hasSession()) {
                $request->session()->forget('tenant_slug');
            }
        }

        return null;
    }

    protected function extractSlugFromRequest(Request $request): ?string
    {
        $slug = $request->route('slug');
        if (is_string($slug) && $slug !== '') {
            return $slug;
        }

        $segment1 = strtolower((string) $request->segment(1));
        if (! in_array($segment1, ['workspace', 'customer', 't'], true)) {
            return null;
        }

        $segment2 = $request->segment(2);

        return is_string($segment2) && $segment2 !== '' ? $segment2 : null;
    }

    protected function getSessionSlug(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $slug = $request->session()->get('tenant_slug');

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    protected function setSessionSlug(Request $request, string $slug): void
    {
        if (! $request->hasSession()) {
            return;
        }

        if ($request->session()->get('tenant_slug') !== $slug) {
            $request->session()->put('tenant_slug', $slug);
        }
    }
}
