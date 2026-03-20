<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantCommercialEligibility
{
    public const BLOCKED_ACCESS_MESSAGE = 'Seu ambiente foi criado, mas ainda não está liberado para uso. É necessário definir um plano e uma assinatura ativos.';

    private const EXPIRED_TRIAL_ALLOWED_ROUTE_NAMES = [
        'tenant.dashboard',
        'tenant.subscription.show',
        'tenant.plan-change-request.create',
        'tenant.plan-change-request.store',
    ];

    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isEligibleForAccess()) {
            return $next($request);
        }

        $blockedMessage = method_exists($tenant, 'commercialAccessBlockedMessage')
            ? $tenant->commercialAccessBlockedMessage()
            : self::BLOCKED_ACCESS_MESSAGE;

        if ($this->tenantHasExpiredTrial($tenant)) {
            if ($this->isAllowedRouteForExpiredTrial($request)) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $blockedMessage,
                    'trial_expired' => true,
                ], 403);
            }

            return redirect()
                ->route('tenant.dashboard', ['slug' => $tenant->subdomain])
                ->with('warning', $blockedMessage);
        }

        $this->clearTenantSession($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $blockedMessage,
            ], 403);
        }

        return redirect()->route('tenant.login', ['slug' => $tenant->subdomain])
            ->with('error', $blockedMessage);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        $currentTenant = tenant();
        if ($currentTenant instanceof Tenant) {
            return $currentTenant;
        }

        $slug = $request->route('slug') ?? session('tenant_slug');
        if (is_string($slug) && $slug !== '') {
            $tenantFromSlug = Tenant::where('subdomain', $slug)->first();
            if ($tenantFromSlug) {
                return $tenantFromSlug;
            }
        }

        $user = Auth::guard('tenant')->user();
        if ($user && $user->tenant) {
            return $user->tenant;
        }

        return null;
    }

    protected function tenantHasExpiredTrial(Tenant $tenant): bool
    {
        if (method_exists($tenant, 'commercialAccessStatusKey')) {
            return $tenant->commercialAccessStatusKey() === 'trial_expired';
        }

        return method_exists($tenant, 'expiredTrialSubscription')
            ? (bool) $tenant->expiredTrialSubscription()
            : false;
    }

    protected function isAllowedRouteForExpiredTrial(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        return in_array($routeName, self::EXPIRED_TRIAL_ALLOWED_ROUTE_NAMES, true);
    }

    protected function clearTenantSession(Request $request): void
    {
        Auth::guard('tenant')->logout();

        $request->session()->forget([
            'tenant_slug',
            'login.id',
            'login.remember',
            'login.tenant_id',
            'two_factor_code_sent',
        ]);

        $request->session()->regenerateToken();
    }
}
