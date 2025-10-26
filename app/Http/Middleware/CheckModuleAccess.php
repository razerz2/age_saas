<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\Module;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module)
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar autenticado para acessar o sistema.');
        }

        $user = auth()->user();

        // Verifica se o usuário não tem acesso ao módulo solicitado
        if (!in_array($module, $user->modules ?? [])) {

            // Busca o nome do módulo (ex: "Faturas", "Usuários", etc.)
            $moduleName = Module::getName($module) ?? ucfirst($module);

            // Mensagem personalizada com o nome do módulo
            $message = "🚫 Você não tem permissão para acessar o módulo **{$moduleName}**.";

            // Redireciona de volta ao dashboard com a mensagem
            return redirect()
                ->route('Platform.dashboard')
                ->with('error', $message);
        }

        return $next($request);
    }
}
