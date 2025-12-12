<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Services\TwoFactorCodeService;
use App\Notifications\TwoFactorCodeNotification;

class TwoFactorChallengeController extends Controller
{
    /**
     * Exibe a página de verificação do código 2FA
     */
    public function create(): View
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        $user = \App\Models\Platform\User::find(session('login.id'));
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Se o método for email ou whatsapp, envia código automaticamente
        $method = $user->two_factor_method;
        if (in_array($method, ['email', 'whatsapp']) && !session('two_factor_code_sent')) {
            $codeService = app(TwoFactorCodeService::class);
            $code = $codeService->generateCode($user, $method);
            $user->notify(new TwoFactorCodeNotification($code, $method));
            session(['two_factor_code_sent' => true]);
        }

        return view('auth.two-factor-challenge', [
            'method' => $method ?? 'totp',
            'user' => $user,
        ]);
    }

    /**
     * Verifica o código 2FA e completa o login
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('login.id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\Platform\User::find($userId);
        
        if (!$user) {
            return redirect()->route('login');
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
        Auth::loginUsingId($userId, session('login.remember', false));
        
        // Limpa a sessão temporária
        session()->forget(['login.id', 'login.remember']);

        $request->session()->regenerate();

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    /**
     * Reenvia o código 2FA
     */
    public function resend(): RedirectResponse
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        $user = \App\Models\Platform\User::find(session('login.id'));
        
        if (!$user) {
            return redirect()->route('login');
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

