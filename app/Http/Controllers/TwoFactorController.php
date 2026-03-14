<?php

namespace App\Http\Controllers;

use App\Notifications\Platform\TwoFactorCodeOfficialNotification;
use App\Services\TwoFactorCodeService;
use App\Support\PlatformTwoFactorPhoneResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Exibe a pagina de configuracao do 2FA
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('profile.two-factor.index', [
            'user' => $user,
            'qrCodeUrl' => $user->two_factor_secret ? $user->getTwoFactorQrCodeUrl() : null,
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
            'whatsappTwoFactorAvailable' => $this->resolvedWhatsAppPhone($user) !== null,
        ]);
    }

    /**
     * Gera uma nova chave secreta para 2FA
     */
    public function generateSecret(): RedirectResponse
    {
        $user = Auth::user();

        $secret = $user->generateTwoFactorSecret();
        $recoveryCodes = $user->generateRecoveryCodes();

        Session::flash('two_factor_secret', $secret);
        Session::flash('two_factor_recovery_codes', $recoveryCodes);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', 'Chave secreta gerada com sucesso! Escaneie o QR Code e confirme o codigo para ativar.');
    }

    /**
     * Confirma e ativa o 2FA apos verificar o codigo
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Chave secreta nao encontrada. Gere uma nova chave primeiro.']);
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            $user->enableTwoFactor();
            $user->refresh();

            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Autenticacao de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Codigo invalido. Verifique e tente novamente.']);
    }

    /**
     * Desativa o 2FA
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->disableTwoFactor();

        return redirect()->route('Platform.two-factor.index')
            ->with('success', 'Autenticacao de dois fatores desativada com sucesso!');
    }

    /**
     * Regenera os codigos de recuperacao
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return back()->withErrors(['password' => '2FA nao esta ativado.']);
        }

        $recoveryCodes = $user->generateRecoveryCodes();
        Session::flash('two_factor_recovery_codes', $recoveryCodes);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', 'Codigos de recuperacao regenerados com sucesso!');
    }

    /**
     * Define o metodo de 2FA (totp, email, whatsapp)
     * Para email/whatsapp, envia codigo automaticamente para confirmacao
     */
    public function setMethod(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:totp,email,whatsapp'],
        ]);

        $user = Auth::user();
        $method = $request->method;

        if ($method === 'totp') {
            $user->two_factor_method = $method;
            $user->save();

            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Metodo de 2FA atualizado. Gere o QR Code para ativar.');
        }

        if ($method === 'email' && !$user->email) {
            return back()->withErrors(['method' => 'E necessario ter um e-mail cadastrado para usar 2FA por e-mail.']);
        }

        if ($method === 'whatsapp' && $this->resolvedWhatsAppPhone($user) === null) {
            $this->logUnavailableWhatsApp2fa($user);
            return back()->withErrors(['method' => '2FA por WhatsApp indisponivel: usuario Platform sem telefone apto para envio oficial.']);
        }

        $user->two_factor_method = $method;
        $user->save();

        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);

        $user->notify(new TwoFactorCodeOfficialNotification($code, $method));

        Session::flash('two_factor_pending_method', $method);
        Session::flash('two_factor_pending_activation', true);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', "Codigo de verificacao enviado via {$method}. Digite o codigo recebido para ativar o 2FA.");
    }

    /**
     * Ativa 2FA com metodo de codigo enviado (email/whatsapp)
     */
    public function activateWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,whatsapp'],
        ]);

        $user = Auth::user();
        $method = $request->method;

        if ($method === 'email' && !$user->email) {
            return back()->withErrors(['method' => 'E necessario ter um e-mail cadastrado para usar 2FA por e-mail.']);
        }

        if ($method === 'whatsapp' && $this->resolvedWhatsAppPhone($user) === null) {
            $this->logUnavailableWhatsApp2fa($user);
            return back()->withErrors(['method' => '2FA por WhatsApp indisponivel: usuario Platform sem telefone apto para envio oficial.']);
        }

        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);

        $user->notify(new TwoFactorCodeOfficialNotification($code, $method));

        Session::flash('two_factor_pending_method', $method);
        Session::flash('two_factor_pending_activation', true);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', "Codigo de verificacao enviado via {$method}. Digite o codigo recebido para ativar o 2FA.");
    }

    /**
     * Confirma ativacao com codigo enviado
     */
    public function confirmWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        $method = session('two_factor_pending_method', 'email');

        $codeService = app(TwoFactorCodeService::class);

        if ($codeService->verifyCode($user, $request->code, $method)) {
            $user->two_factor_method = $method;
            $user->enableTwoFactor();
            $user->refresh();

            Session::forget(['two_factor_pending_method', 'two_factor_pending_activation']);

            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Autenticacao de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Codigo invalido ou expirado. Verifique e tente novamente.']);
    }

    private function resolvedWhatsAppPhone(object $user): ?string
    {
        return app(PlatformTwoFactorPhoneResolver::class)->resolve($user);
    }

    private function logUnavailableWhatsApp2fa(object $user): void
    {
        $resolved = app(PlatformTwoFactorPhoneResolver::class)->resolveWithReason($user);

        Log::warning('platform_2fa_whatsapp_unavailable_on_settings', [
            'user_id' => $user->id ?? null,
            'user_type' => get_class($user),
            'reason' => $resolved['reason'],
        ]);
    }
}
