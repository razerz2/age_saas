<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWhatsAppOfficialProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = function_exists('sysconfig')
            ? (string) sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : (string) config('services.whatsapp.provider', 'whatsapp_business');

        $provider = strtolower(trim($provider));
        if ($provider === '') {
            $provider = 'whatsapp_business';
        }

        if (in_array($provider, ['whatsapp_business', 'business'], true)) {
            return $next($request);
        }

        $message = 'Módulo indisponível: provider ativo incompatível. Este recurso exige WHATSAPP_PROVIDER=whatsapp_business.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'provider' => $provider,
            ], 422);
        }

        return redirect()
            ->route('Platform.dashboard')
            ->with('error', $message);
    }
}