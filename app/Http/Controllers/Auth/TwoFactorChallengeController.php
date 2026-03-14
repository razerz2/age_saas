<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Platform\User;
use App\Notifications\Platform\TwoFactorCodeOfficialNotification;
use App\Providers\RouteServiceProvider;
use App\Services\TwoFactorCodeService;
use App\Support\PlatformTwoFactorPhoneResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    /**
     * Exibe a pagina de verificacao do codigo 2FA
     */
    public function create(): RedirectResponse|View
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('login.id'));
        if (!$user) {
            return redirect()->route('login');
        }

        $configuredMethod = (string) ($user->two_factor_method ?: 'totp');
        $runtimeMethod = $this->resolveRuntimeMethod($user, $configuredMethod);
        session(['two_factor_runtime_method' => $runtimeMethod]);

        if (
            $configuredMethod === 'whatsapp'
            && $runtimeMethod === 'email'
            && !session()->has('two_factor_runtime_fallback_alerted')
        ) {
            session()->flash('warning', '2FA por WhatsApp indisponivel para este usuario. Codigo enviado por e-mail.');
            session(['two_factor_runtime_fallback_alerted' => true]);
        }

        if (in_array($runtimeMethod, ['email', 'whatsapp'], true) && !session('two_factor_code_sent')) {
            $codeService = app(TwoFactorCodeService::class);
            $code = $codeService->generateCode($user, $runtimeMethod);
            $user->notify(new TwoFactorCodeOfficialNotification($code, $runtimeMethod));
            session(['two_factor_code_sent' => true]);
        }

        return view('auth.two-factor-challenge', [
            'method' => $runtimeMethod,
            'user' => $user,
        ]);
    }

    /**
     * Verifica o codigo 2FA e completa o login
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

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $code = $request->code;
        $method = (string) session('two_factor_runtime_method', $user->two_factor_method ?? 'totp');
        $isValid = false;

        if ($method === 'totp') {
            if ($user->verifyTwoFactorCode($code)) {
                $isValid = true;
            } elseif ($user->useRecoveryCode($code)) {
                $isValid = true;
            }
        } else {
            $codeService = app(TwoFactorCodeService::class);
            if ($codeService->verifyCode($user, $code, $method)) {
                $isValid = true;
            } elseif ($user->useRecoveryCode($code)) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return back()->withErrors(['code' => 'Codigo invalido ou expirado. Verifique e tente novamente.']);
        }

        session()->forget(['two_factor_code_sent', 'two_factor_runtime_method', 'two_factor_runtime_fallback_alerted']);

        Auth::loginUsingId($userId, session('login.remember', false));
        session()->forget(['login.id', 'login.remember']);

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Reenvia o codigo 2FA
     */
    public function resend(): RedirectResponse
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('login.id'));
        if (!$user) {
            return redirect()->route('login');
        }

        $configuredMethod = (string) ($user->two_factor_method ?: 'totp');
        $method = $this->resolveRuntimeMethod($user, $configuredMethod);
        session(['two_factor_runtime_method' => $method]);

        if (!in_array($method, ['email', 'whatsapp'], true)) {
            return back()->withErrors(['method' => 'Reenvio disponivel apenas para metodos de codigo enviado (email/whatsapp).']);
        }

        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);
        $user->notify(new TwoFactorCodeOfficialNotification($code, $method));
        session(['two_factor_code_sent' => true]);

        return back()->with('success', "Codigo reenviado via {$method}!");
    }

    private function resolveRuntimeMethod(User $user, string $configuredMethod): string
    {
        if ($configuredMethod !== 'whatsapp') {
            return $configuredMethod;
        }

        $resolved = app(PlatformTwoFactorPhoneResolver::class)->resolveWithReason($user);
        if ($resolved['phone'] !== null) {
            return 'whatsapp';
        }

        Log::warning('platform_2fa_whatsapp_unavailable_on_challenge', [
            'user_id' => $user->id,
            'reason' => $resolved['reason'],
            'fallback' => $user->email ? 'email' : 'none',
        ]);

        if (!empty($user->email)) {
            return 'email';
        }

        return 'whatsapp';
    }
}
