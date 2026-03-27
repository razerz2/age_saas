<?php

namespace App\Http\Middleware\Tenant;

use App\Services\Tenant\TenantWhatsAppConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwnWhatsAppOfficialProvider
{
    public function __construct(
        private readonly TenantWhatsAppConfigService $tenantWhatsAppConfigService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenantWhatsAppConfigService->isOwnOfficialProviderEnabled()) {
            return $next($request);
        }

        $message = 'Modulo indisponivel: habilite WhatsApp proprio (driver tenancy) com provedor oficial Meta para acessar Templates Oficiais.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return redirect()
            ->route('tenant.settings.index', ['slug' => tenant()->subdomain, 'tab' => 'notificacoes'])
            ->with('error', $message);
    }
}

