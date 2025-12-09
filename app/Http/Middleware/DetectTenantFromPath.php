<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;

class DetectTenantFromPath
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info("📌 DetectTenantFromPath iniciado", [
            'url' => $request->fullUrl(),
            'segment1' => $request->segment(1),
            'segment2' => $request->segment(2),
        ]);

        $segment1 = $request->segment(1);
        
        // Verifica se é um dos novos prefixos comerciais (customer ou workspace)
        if (in_array($segment1, ['customer', 'workspace'])) {

            $slug = $request->segment(2);

            \Log::info("🔍 Detectando tenant pelo PATH", [
                'prefix' => $segment1,
                'slug' => $slug
            ]);

            if ($slug && is_string($slug)) {

                $tenant = Tenant::where('subdomain', $slug)->first();

                if ($tenant) {

                    \Log::info("✅ DetectTenantFromPath encontrou tenant", [
                        'id'  => $tenant->id,
                        'slug' => $tenant->subdomain
                    ]);

                    $tenant->makeCurrent();
                    session(['tenant_slug' => $tenant->subdomain]);
                } else {

                    \Log::warning("⚠️ Slug inválido. Limpando session tenant_slug", [
                        'slug' => $slug
                    ]);

                    session()->forget('tenant_slug');
                }
            }
        }

        return $next($request);
    }
}
