<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FeatureAccessService;
use App\Models\Platform\Tenant;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyFeatureAccess
{
    protected FeatureAccessService $featureService;

    public function __construct(FeatureAccessService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Handle an incoming request.
     * Permite acesso se o tenant tiver QUALQUER uma das features especificadas
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$features  Nomes das funcionalidades (basta ter uma)
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            abort(403, 'Tenant não encontrado');
        }

        // Se nenhuma feature foi especificada, permite acesso
        if (empty($features)) {
            return $next($request);
        }

        // Verifica se o tenant tem acesso a pelo menos uma das features
        if (!$this->featureService->hasAnyFeature($features, $tenant)) {
            $featureList = implode(', ', $features);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acesso negado: nenhuma das funcionalidades está disponível no seu plano',
                    'required_features' => $features,
                ], 403);
            }

            abort(403, "Acesso negado: nenhuma das funcionalidades está disponível no seu plano. Funcionalidades requeridas: {$featureList}");
        }

        return $next($request);
    }
}

