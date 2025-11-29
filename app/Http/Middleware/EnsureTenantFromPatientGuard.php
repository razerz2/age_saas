<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

class EnsureTenantFromPatientGuard
{
    public function handle(Request $request, Closure $next)
    {
        Log::info("📌 EnsureTenantFromPatientGuard → início", [
            'tenant_current' => Tenant::current()?->id,
            'auth_check'     => Auth::guard('patient')->check(),
            'session_slug'   => session('tenant_slug')
        ]);

        // 1) Tenant já está ativo → segue
        if (Tenant::current()) {
            Log::info("➡️ Tenant já ativo para portal do paciente, continuando...");
            return $next($request);
        }

        // 2) Se o paciente estiver logado, obtém o tenant da sessão
        if (Auth::guard('patient')->check()) {
            
            // O tenant deve estar salvo na sessão do login
            $slug = session('tenant_slug');
            
            if ($slug) {
                $tenant = Tenant::where('subdomain', $slug)->first();
                
                if ($tenant) {
                    Log::info("🔁 Ativando tenant via sessão do paciente", [
                        'tenant_id' => $tenant->id,
                        'slug'      => $tenant->subdomain
                    ]);
                    
                    $tenant->makeCurrent();
                    
                    // Mantém o slug na sessão
                    session(['tenant_slug' => $tenant->subdomain]);
                } else {
                    Log::warning("⚠️ Tenant não encontrado pela sessão", ['slug' => $slug]);
                    Auth::guard('patient')->logout();
                    return redirect('/')->withErrors(['tenant' => 'Clínica não encontrada.']);
                }
            } else {
                Log::warning("⚠️ Não há tenant_slug na sessão do paciente autenticado");
                Auth::guard('patient')->logout();
                return redirect('/')->withErrors(['tenant' => 'Sessão expirada. Por favor, faça login novamente.']);
            }
        } else {
            // Se não está autenticado, tenta usar o slug da sessão
            $slug = session('tenant_slug');
            
            if ($slug) {
                $tenant = Tenant::where('subdomain', $slug)->first();
                
                if ($tenant) {
                    Log::info("🔁 Ativando tenant via sessão (não autenticado)", [
                        'slug' => $slug
                    ]);
                    $tenant->makeCurrent();
                }
            }
        }

        return $next($request);
    }
}

