<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

class DetectTenantForPatientPortal
{
    public function handle(Request $request, Closure $next)
    {
        Log::info("📌 DetectTenantForPatientPortal iniciado", [
            'url' => $request->fullUrl(),
            'host' => $request->getHost(),
            'segment1' => $request->segment(1),
        ]);

        $tenant = null;

        // 1. Tenta detectar pelo subdomínio (URL customizada)
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        }

        // 2. Tenta usar a sessão como fallback
        if (!$tenant) {
            $slug = session('tenant_slug');
            if ($slug) {
                $tenant = Tenant::where('subdomain', $slug)->first();
            }
        }

        if ($tenant) {
            // 🔒 Validação de Status do Tenant e da Rede
            if ($tenant->status !== 'active' && $tenant->status !== 'trial') {
                \Log::warning("🚫 Portal do Paciente: Acesso bloqueado - Tenant '{$tenant->subdomain}' status '{$tenant->status}'");
                abort(403, 'Esta clínica está temporariamente indisponível. Por favor, tente novamente mais tarde.');
            }

            if ($tenant->network_id) {
                $network = $tenant->network;
                if ($network && !$network->is_active) {
                    \Log::warning("🚫 Portal do Paciente: Acesso bloqueado - Rede '{$network->name}' inativa");
                    abort(403, 'O portal desta clínica está temporariamente indisponível.');
                }
            }

            Log::info("✅ Tenant detectado para portal do paciente", [
                'id' => $tenant->id,
                'subdomain' => $tenant->subdomain
            ]);

            $tenant->makeCurrent();
            session(['tenant_slug' => $tenant->subdomain]);
            return $next($request);
        }

        Log::warning("⚠️ Não foi possível detectar o tenant para o portal do paciente");

        // Se não conseguir detectar, retorna erro
        return redirect('/')->withErrors(['tenant' => 'Não foi possível identificar a clínica.']);
    }

    /**
     * Extrai o subdomínio do host
     * Exemplo: tenant1.example.com -> tenant1
     */
    private function extractSubdomain(string $host): ?string
    {
        // Remove porta se existir
        $host = explode(':', $host)[0];

        // Remove 'www.' se existir
        $host = preg_replace('/^www\./', '', $host);

        // Divide o host em partes
        $parts = explode('.', $host);

        // Se tiver mais de 2 partes, a primeira é o subdomínio
        // Exemplo: tenant1.example.com -> ['tenant1', 'example', 'com']
        if (count($parts) >= 3) {
            return $parts[0];
        }

        // Para desenvolvimento local (ex: tenant1.localhost)
        if (count($parts) === 2 && in_array($parts[1], ['localhost', 'test', 'local'])) {
            return $parts[0];
        }

        return null;
    }
}
