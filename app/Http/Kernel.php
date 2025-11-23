<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Middlewares globais
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Grupos de middlewares
     */
    protected $middlewareGroups = [

        /**
         * ⭐ Plataforma (web)
         * Não carregamos tenant aqui!
         */
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        /**
         * ⭐ Rotas tenant-web
         * Aqui carregamos o tenant ANTES das sessões e auth.
         */
        'tenant-web' => [
            \App\Http\Middleware\DetectTenantFromPath::class,    // 🔥 detecta tenant /t/{tenant}
            \App\Http\Middleware\PersistTenantInSession::class,  // salva tenant na session
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Aliases de middlewares
     */
    protected $middlewareAliases = [

        // Laravel padrão
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive'     => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed'           => \App\Http\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Middlewares da aplicação
        'verify.asaas.token' => \App\Http\Middleware\VerifyAsaasToken::class,
        'module.access'      => \App\Http\Middleware\CheckModuleAccess::class,
        'ensure.guard'       => \App\Http\Middleware\EnsureCorrectGuard::class,

        /**
         * 🔥 Tenant detectado pelo path
         */
        'tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,

        /**
         * 🔐 Autenticação exclusiva do tenant
         */
        'tenant.auth' => \App\Http\Middleware\RedirectIfTenantUnauthenticated::class,

        /**
         * ⭐ Setar tenant automaticamente após login
         */
        'tenant.from.guard' => \App\Http\Middleware\EnsureTenantFromGuard::class,

        /**
         * 🧠 Persistência do tenant entre requests
         */
        'persist.tenant' => \App\Http\Middleware\PersistTenantInSession::class,
    ];
}
