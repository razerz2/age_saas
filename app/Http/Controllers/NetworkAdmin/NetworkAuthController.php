<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\NetworkUser;

class NetworkAuthController extends Controller
{
    /**
     * Exibe formulário de login
     */
    public function showLoginForm()
    {
        $network = app('currentNetwork');

        // Se já estiver autenticado, redireciona para dashboard
        if (Auth::guard('network')->check()) {
            return redirect()->route('network.dashboard', ['network' => $network->slug]);
        }

        return view('network-admin.auth.login', compact('network'));
    }

    /**
     * Processa login
     */
    public function login(Request $request)
    {
        $network = app('currentNetwork');

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Busca usuário da rede
        $user = NetworkUser::where('email', $credentials['email'])
            ->where('clinic_network_id', $network->id)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas ou usuário inativo.',
            ])->withInput($request->only('email'));
        }

        // Define guard network
        Auth::shouldUse('network');

        // Tenta autenticar
        if (Auth::guard('network')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('network.dashboard', ['network' => $network->slug]));
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $network = app('currentNetwork');
        Auth::guard('network')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('network.login', ['network' => $network->slug]);
    }
}

