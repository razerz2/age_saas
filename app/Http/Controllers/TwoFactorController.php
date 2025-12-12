<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Services\TwoFactorCodeService;
use App\Notifications\TwoFactorCodeNotification;

class TwoFactorController extends Controller
{
    /**
     * Exibe a página de configuração do 2FA
     */
    public function index(): View
    {
        $user = Auth::user();
        
        return view('profile.two-factor.index', [
            'user' => $user,
            'qrCodeUrl' => $user->two_factor_secret ? $user->getTwoFactorQrCodeUrl() : null,
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
        ]);
    }

    /**
     * Gera uma nova chave secreta para 2FA
     */
    public function generateSecret(): RedirectResponse
    {
        $user = Auth::user();
        
        // Gera nova chave secreta
        $secret = $user->generateTwoFactorSecret();
        
        // Gera códigos de recuperação
        $recoveryCodes = $user->generateRecoveryCodes();
        
        Session::flash('two_factor_secret', $secret);
        Session::flash('two_factor_recovery_codes', $recoveryCodes);
        
        return redirect()->route('Platform.two-factor.index')
            ->with('success', 'Chave secreta gerada com sucesso! Escaneie o QR Code e confirme o código para ativar.');
    }

    /**
     * Confirma e ativa o 2FA após verificar o código
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        
        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Chave secreta não encontrada. Gere uma nova chave primeiro.']);
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            $user->enableTwoFactor();
            
            // Recarrega o usuário para garantir que os dados estão atualizados
            $user->refresh();
            
            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Autenticação de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Código inválido. Verifique e tente novamente.']);
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
            ->with('success', 'Autenticação de dois fatores desativada com sucesso!');
    }

    /**
     * Regenera os códigos de recuperação
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return back()->withErrors(['password' => '2FA não está ativado.']);
        }

        $recoveryCodes = $user->generateRecoveryCodes();
        
        Session::flash('two_factor_recovery_codes', $recoveryCodes);
        
        return redirect()->route('Platform.two-factor.index')
            ->with('success', 'Códigos de recuperação regenerados com sucesso!');
    }

    /**
     * Define o método de 2FA (totp, email, whatsapp)
     * Para email/whatsapp, envia código automaticamente para confirmação
     */
    public function setMethod(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:totp,email,whatsapp'],
        ]);

        $user = Auth::user();
        $method = $request->method;
        
        // Se o método for TOTP, apenas salva o método
        if ($method === 'totp') {
            $user->two_factor_method = $method;
            $user->save();
            
            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Método de 2FA atualizado. Gere o QR Code para ativar.');
        }
        
        // Para email/whatsapp, verifica se o usuário tem os dados necessários
        if ($method === 'email' && !$user->email) {
            return back()->withErrors(['method' => 'É necessário ter um e-mail cadastrado para usar 2FA por e-mail.']);
        }

        if ($method === 'whatsapp' && !$user->telefone) {
            return back()->withErrors(['method' => 'É necessário ter um telefone cadastrado para usar 2FA por WhatsApp.']);
        }

        // Salva o método escolhido
        $user->two_factor_method = $method;
        $user->save();

        // Gera código e envia automaticamente
        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);

        // Envia notificação
        $user->notify(new TwoFactorCodeNotification($code, $method));

        Session::flash('two_factor_pending_method', $method);
        Session::flash('two_factor_pending_activation', true);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', "Código de verificação enviado via {$method}. Digite o código recebido para ativar o 2FA.");
    }

    /**
     * Ativa 2FA com método de código enviado (email/whatsapp)
     */
    public function activateWithCode(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,whatsapp'],
        ]);

        $user = Auth::user();
        $method = $request->method;

        // Verifica se o usuário tem email ou telefone conforme o método
        if ($method === 'email' && !$user->email) {
            return back()->withErrors(['method' => 'É necessário ter um e-mail cadastrado para usar 2FA por e-mail.']);
        }

        if ($method === 'whatsapp' && !$user->telefone) {
            return back()->withErrors(['method' => 'É necessário ter um telefone cadastrado para usar 2FA por WhatsApp.']);
        }

        // Gera código e envia
        $codeService = app(TwoFactorCodeService::class);
        $code = $codeService->generateCode($user, $method);

        // Envia notificação
        $user->notify(new TwoFactorCodeNotification($code, $method));

        Session::flash('two_factor_pending_method', $method);
        Session::flash('two_factor_pending_activation', true);

        return redirect()->route('Platform.two-factor.index')
            ->with('success', "Código de verificação enviado via {$method}. Digite o código recebido para ativar o 2FA.");
    }

    /**
     * Confirma ativação com código enviado
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
            
            // Recarrega o usuário para garantir que os dados estão atualizados
            $user->refresh();
            
            Session::forget(['two_factor_pending_method', 'two_factor_pending_activation']);
            
            return redirect()->route('Platform.two-factor.index')
                ->with('success', 'Autenticação de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Código inválido ou expirado. Verifique e tente novamente.']);
    }
}

