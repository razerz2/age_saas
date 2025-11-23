<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $slug = $request->route('tenant');

        \Log::info("📌 Exibindo login form para slug", ['slug' => $slug]);

        $tenant = Tenant::where('subdomain', $slug)->first();

        return view('tenant.auth.login', compact('tenant'));
    }

    public function login(Request $request)
    {
        \Log::info('===== INÍCIO LOGIN =====');

        $tenantSlug = $request->route('tenant');

        \Log::info('Slug recebido na rota', ['slug' => $tenantSlug]);

        $tenant = Tenant::where('subdomain', $tenantSlug)->first();

        \Log::info('Tenant encontrado?', [
            'exists' => (bool)$tenant,
            'tenant' => $tenant?->toArray()
        ]);

        if (!$tenant) {
            return back()->withErrors(['email' => 'Tenant inválido.']);
        }

        Auth::shouldUse('tenant');
        \Log::info('Guard forçado para tenant');

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        \Log::info("Credenciais", ['email' => $credentials['email']]);

        $user = \App\Models\Tenant\User::where('email', $credentials['email'])->first();

        \Log::info("Usuário encontrado?", [
            'exists' => (bool)$user,
            'user' => $user?->toArray()
        ]);

        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado nesta clínica.']);
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Usuário desativado.']);
        }

        $attempt = Auth::guard('tenant')->attempt($credentials, $request->boolean('remember'));

        \Log::info('Tentativa de login', ['success' => $attempt]);

        if ($attempt) {

            \Log::info('Login OK — ativando tenant', [
                'tenant_id' => $tenant->id
            ]);

            $tenant->makeCurrent();

            session(['tenant_slug' => $tenant->subdomain]);

            $request->session()->regenerate();

            \Log::info('Redirecionando ao dashboard');

            return redirect()->route('tenant.dashboard');
        }

        return back()->withErrors(['email' => 'Senha incorreta.']);
    }

    public function logout(Request $request)
    {
        $tenant = tenant();

        \Log::info("Logout requisitado", [
            'tenant_current' => $tenant?->id
        ]);

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login', [
            'tenant' => $tenant->subdomain ?? ''
        ]);
    }
}
