<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Garante que o usuário está autenticado na rede
 * Define o guard 'network' como padrão
 * Alias: network.auth
 */
class EnsureNetworkUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Garante que a rede foi detectada
        if (!app()->bound('currentNetwork')) {
            abort(404, 'Rede de clínicas não encontrada');
        }

        // Define o guard network como padrão
        Auth::shouldUse('network');

        // Verifica autenticação
        if (!Auth::guard('network')->check()) {
            return redirect()->route('network.login');
        }

        // Verifica se o usuário pertence à rede detectada
        $network = app('currentNetwork');
        $user = Auth::guard('network')->user();

        if ($user && $user->clinic_network_id !== $network->id) {
            Auth::guard('network')->logout();
            return redirect()->route('network.login')->withErrors(['email' => 'Acesso negado.']);
        }

        return $next($request);
    }
}
