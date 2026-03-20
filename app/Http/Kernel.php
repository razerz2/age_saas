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
         * Detecta rede de clínicas por subdomínio ANTES de qualquer middleware de tenant
         */
        'web' => [
            \App\Http\Middleware\EnsureNoTenantForLanding::class,  // Garante que landing page não tem tenant
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
        'verify.asaas.webhook.secret' => \App\Http\Middleware\VerifyAsaasWebhookSecret::class,
        'verify.asaas.webhook.ip' => \App\Http\Middleware\VerifyAsaasWebhookIpWhitelist::class,
        'throttle.asaas.webhook' => \App\Http\Middleware\ThrottleAsaasWebhook::class,
        'module.access'      => \App\Http\Middleware\CheckModuleAccess::class,
        'whatsapp.official.provider' => \App\Http\Middleware\EnsureWhatsAppOfficialProvider::class,
        'ensure.guard'       => \App\Http\Middleware\EnsureCorrectGuard::class,
        'platform.bot.token' => \App\Http\Middleware\Platform\BotApiTokenMiddleware::class,

        /**
         * 🔥 Tenant detectado pelo path
         */
        'tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,

        /**
         * 🔐 Autenticação exclusiva do tenant
         */
        'tenant.auth' => \App\Http\Middleware\RedirectIfTenantUnauthenticated::class,
        
        /**
         * 🏥 Autenticação exclusiva do paciente
         */
        'patient.auth' => \App\Http\Middleware\RedirectIfPatientUnauthenticated::class,

        /**
         * ⭐ Setar tenant automaticamente após login
         */
        'tenant.from.guard' => \App\Http\Middleware\EnsureTenantFromGuard::class,
        
        /**
         * 🏥 Setar tenant automaticamente do paciente autenticado
         */
        'patient.tenant.from.guard' => \App\Http\Middleware\EnsureTenantFromPatientGuard::class,

        /**
         * 🧠 Persistência do tenant entre requests
         */
        'persist.tenant' => \App\Http\Middleware\PersistTenantInSession::class,

        /**
         * 🏥 Detecta tenant para rotas do portal do paciente
         */
        'detect.tenant.patient' => \App\Http\Middleware\DetectTenantForPatientPortal::class,

        /**
         * 🔐 Verifica acesso a funcionalidades do plano (requer TODAS as features)
         */
        'feature' => \App\Http\Middleware\EnsureFeatureAccess::class,

        /**
         * 🔐 Verifica acesso a funcionalidades do plano (requer QUALQUER feature)
         */
        'feature.any' => \App\Http\Middleware\EnsureAnyFeatureAccess::class,

        /**
         * 📣 Verifica se módulo de campanhas está disponível no tenant
         */
        'campaign.module.enabled' => \App\Http\Middleware\Tenant\EnsureCampaignModuleEnabled::class,
        'tenant.commercial.eligibility' => \App\Http\Middleware\EnsureTenantCommercialEligibility::class,

        
    ];
}
