<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\ClinicNetwork;

class DetectClinicNetworkFromSubdomain
{
    /**
     * Handle an incoming request.
     *
     * Detecta rede de clínicas pelo subdomínio, mas NUNCA ativa tenant.
     * Rede apenas é detectada e disponibilizada via app('currentNetwork').
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Ignora requests que já são de rotas específicas do tenant
        // Isso evita conflito com PathTenantFinder
        $path = $request->path();
        if (
            str_starts_with($path, 'customer/') ||
            str_starts_with($path, 'workspace/') ||
            str_starts_with($path, 't/')
        ) {
            return $next($request);
        }

        // Lê o host
        $host = $request->getHost();

        // Ignora domínios principais (sem subdomínio)
        // Ex: allsync.com.br, www.allsync.com.br
        if (in_array($host, ['allsync.com.br', 'www.allsync.com.br', 'localhost', '127.0.0.1'])) {
            return $next($request);
        }

        // Extrai subdomínio
        // Suporta: rede.allsync.com.br ou admin.rede.allsync.com.br
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        // Se for 'admin', pega o próximo subdomínio
        if ($subdomain === 'admin' && isset($parts[1])) {
            $subdomain = $parts[1];
        }

        if (!$subdomain || $subdomain === 'www') {
            return $next($request);
        }

        // Busca rede pelo slug
        $network = ClinicNetwork::where('slug', $subdomain)
            ->where('is_active', true)
            ->first();

        \Log::debug("DetectClinicNetwork: Buscando slug {$subdomain} no host {$host}");

        if ($network) {
            \Log::debug("DetectClinicNetwork: Rede encontrada! ID: {$network->id}");
            // Disponibiliza a rede via container
            app()->instance('currentNetwork', $network);
        } else {
            \Log::debug("DetectClinicNetwork: Nenhuma rede ativa encontrada para o slug {$subdomain}");
        }

        // Nunca aborta, apenas segue o request
        return $next($request);
    }
}

