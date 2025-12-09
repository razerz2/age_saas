<?php

namespace App\Http\Controllers\Tenant\PatientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\Tenant;
use App\Models\Tenant\PatientLogin;

class AuthController extends Controller
{
    public function showLoginForm($slug)
    {
        // Verifica se já está autenticado
        if (Auth::guard('patient')->check()) {
            $tenantSlug = tenant()->subdomain ?? $slug;
            return redirect()->route('patient.dashboard', ['slug' => $tenantSlug]);
        }

        return view('tenant.patient_portal.auth.login', compact('tenant'));
    }

    public function login(Request $request, $slug)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        Auth::shouldUse('patient');

        $patientLogin = PatientLogin::where('email', $credentials['email'])->first();

        if (!$patientLogin) {
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->withInput();
        }

        // Verifica se o tenant está ativo
        $tenantModel = Tenant::current();
        if (!$tenantModel) {
            return back()->withErrors(['email' => 'Não foi possível identificar a clínica.'])->withInput();
        }

        // Verifica se o paciente está ativo
        if ($patientLogin->patient && !$patientLogin->patient->is_active) {
            return back()->withErrors(['email' => 'Sua conta está desativada.'])->withInput();
        }

        // Verifica se o login está ativo
        if (!$patientLogin->is_active) {
            return back()->withErrors(['email' => 'Seu acesso ao portal está bloqueado.'])->withInput();
        }

        if (Auth::guard('patient')->attempt($credentials, $request->boolean('remember'))) {
            // Atualiza last_login_at
            $patientLogin->update(['last_login_at' => now()]);

            // Garante que o tenant está salvo na sessão
            session(['tenant_slug' => $tenantModel->subdomain]);

            $request->session()->regenerate();

            return redirect()->route('patient.dashboard', ['slug' => $tenantModel->subdomain]);
        }

        return back()->withErrors(['email' => 'Credenciais inválidas.'])->withInput();
    }

    public function logout(Request $request)
    {
        $tenantSlug = session('tenant_slug');
        
        Auth::guard('patient')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($tenantSlug) {
            return redirect()->route('patient.login', ['slug' => $tenantSlug]);
        }
        
        return redirect('/');
    }

    public function showForgotPasswordForm($slug)
    {
        $tenant = \App\Models\Platform\Tenant::where('subdomain', $slug)->first();
        return view('tenant.patient_portal.auth.forgot-password', compact('tenant'));
    }

    public function showResetPasswordForm($slug, $token)
    {
        $tenant = \App\Models\Platform\Tenant::where('subdomain', $slug)->first();
        return view('tenant.patient_portal.auth.reset-password', ['token' => $token, 'tenant' => $tenant]);
    }
}
