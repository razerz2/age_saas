<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Platform\Tenant;
use App\Services\TwoFactorCodeService;
use App\Notifications\TwoFactorCodeNotification;

class TwoFactorChallengeController extends Controller
{
    /**
     * Exibe a página de verificação do código 2FA
     */
    public function create(Request $request): View|RedirectResponse
    {
        $slug = $request->route('slug') ?? $request->route('tenant');
        
        if (!session('login.id') || !session('login.tenant_id')) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant = Tenant::find(session('login.tenant_id'));
        if (!$tenant) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant->makeCurrent();
        $this->configureTenantDatabaseConnection($tenant);

        $user = \App\Models\Tenant\User::on('tenant')->find(session('login.id'));
        
        if (!$user) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        // Se o método for email ou whatsapp, envia código automaticamente
        $method = $user->two_factor_method;
        if (in_array($method, ['email', 'whatsapp']) && !session('two_factor_code_sent')) {
            $codeService = app(TwoFactorCodeService::class);
            $code = $codeService->generateCode($user, $method);
            $user->notify(new TwoFactorCodeNotification($code, $method));
            session(['two_factor_code_sent' => true]);
        }

        return view('tenant.auth.two-factor-challenge', compact('tenant', 'user', 'method'));
    }

    /**
     * Verifica o código 2FA e completa o login
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $slug = $request->route('slug') ?? $request->route('tenant');
        $userId = session('login.id');
        $tenantId = session('login.tenant_id');

        if (!$userId || !$tenantId) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant->makeCurrent();
        $this->configureTenantDatabaseConnection($tenant);

        $user = \App\Models\Tenant\User::on('tenant')->find($userId);
        
        if (!$user) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        // Verifica o código 2FA
        $code = $request->code;
        $method = $user->two_factor_method ?? 'totp';
        $isValid = false;

        // Verifica conforme o método configurado
        if ($method === 'totp') {
            // TOTP (Google Authenticator)
            if ($user->verifyTwoFactorCode($code)) {
                $isValid = true;
            } elseif ($user->useRecoveryCode($code)) {
                $isValid = true;
            }
        } else {
            // Email ou WhatsApp
            $codeService = app(TwoFactorCodeService::class);
            if ($codeService->verifyCode($user, $code, $method)) {
                $isValid = true;
            } elseif ($user->useRecoveryCode($code)) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return back()->withErrors(['code' => 'Código inválido ou expirado. Verifique e tente novamente.']);
        }

        // Limpa flag de código enviado
        session()->forget('two_factor_code_sent');

        // Completa o login
        Auth::guard('tenant')->loginUsingId($userId, session('login.remember', false));
        
        // Limpa a sessão temporária
        session()->forget(['login.id', 'login.remember', 'login.tenant_id']);

        $request->session()->regenerate();
        session(['tenant_slug' => $tenant->subdomain]);

        return redirect()->route('tenant.dashboard', ['slug' => $tenant->subdomain]);
    }

    /**
     * Configura a conexão com o banco de dados do tenant
     */
    protected function configureTenantDatabaseConnection(Tenant $tenant)
    {
        $dbHost = $tenant->db_host ?: env('DB_TENANT_HOST', '127.0.0.1');
        $dbPort = $tenant->db_port ?: env('DB_TENANT_PORT', '5432');

        \Config::set('database.connections.tenant.host', $dbHost);
        \Config::set('database.connections.tenant.port', $dbPort);
        \Config::set('database.connections.tenant.database', $tenant->db_name);
        \Config::set('database.connections.tenant.username', $tenant->db_username);
        \Config::set('database.connections.tenant.password', $tenant->db_password ?? '');

        \DB::purge('tenant');
        \DB::reconnect('tenant');
    }

    /**
     * Reenvia o código 2FA
     */
    public function resend(Request $request): RedirectResponse
    {
        $slug = $request->route('slug') ?? $request->route('tenant');
        
        if (!session('login.id') || !session('login.tenant_id')) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant = Tenant::find(session('login.tenant_id'));
        if (!$tenant) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $tenant->makeCurrent();
        $this->configureTenantDatabaseConnection($tenant);

        $user = \App\Models\Tenant\User::on('tenant')->find(session('login.id'));
        
        if (!$user) {
            return redirect()->route('tenant.login', ['slug' => $slug]);
        }

        $method = $user->two_factor_method;
        
        if (!in_array($method, ['email', 'whatsapp'])) {
            return back()->withErrors(['method' => 'Reenvio disponível apenas para métodos de código enviado (email/whatsapp).']);
        }

        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);
        $user->notify(new TwoFactorCodeNotification($code, $method));

        return back()->with('success', "Código reenviado via {$method}!");
    }
}

