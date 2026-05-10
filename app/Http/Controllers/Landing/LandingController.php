<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LandingController extends Controller
{
    public function __construct()
    {
        $landingTrialPlan = Plan::publiclyAvailable()
            ->where('trial_enabled', true)
            ->where('trial_days', '>', 0)
            ->orderBy('price_cents', 'asc')
            ->first();

        view()->share('landingTrialPlan', $landingTrialPlan);
    }

    /**
     * Exibe a pagina principal da landing
     */
    public function index()
    {
        $plans = Plan::publiclyAvailable()->get();

        return view('landing.index', compact('plans'));
    }

    /**
     * Exibe a pagina de funcionalidades detalhadas
     */
    public function features()
    {
        return view('landing.features');
    }

    /**
     * Exibe a pagina de planos
     */
    public function plans()
    {
        $plans = Plan::publiclyAvailable()
            ->orderBy('price_cents', 'asc')
            ->get();

        return view('landing.plans', compact('plans'));
    }

    /**
     * Exibe a pagina de contato
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Processa o envio do formulario de contato da landing.
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'in:demo,pricing,support,commercial,other'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'subject.in' => 'Selecione um assunto valido para o contato.',
        ]);

        $subjectLabels = [
            'demo' => 'Solicitar Demonstracao',
            'pricing' => 'Duvidas sobre Planos',
            'support' => 'Suporte Tecnico',
            'commercial' => 'Falar com Comercial',
            'other' => 'Outro',
        ];

        $recipient = trim((string) sysconfig('landing.contact.form_recipient_email', ''));
        if ($recipient === '') {
            $recipient = trim((string) sysconfig('landing.contact.email_primary', 'contato@saas-saude.com.br'));
        }

        $recipientName = trim((string) sysconfig('landing.contact.form_recipient_name', config('app.name', 'AllSync')));
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', config('app.name', 'AllSync'));

        if (!$fromAddress) {
            Log::warning('Formulario de contato nao enviado: MAIL_FROM_ADDRESS nao configurado.');

            return back()
                ->withInput()
                ->withErrors(['contact' => 'Nao foi possivel enviar a mensagem no momento. Tente novamente mais tarde.']);
        }

        $subjectLabel = $subjectLabels[$validated['subject']] ?? 'Contato';
        $mailSubject = "[Landing] {$subjectLabel} - {$validated['name']}";
        $bodyLines = [
            'Novo contato recebido pela landing page.',
            '',
            "Nome: {$validated['name']}",
            "E-mail: {$validated['email']}",
            'Telefone: ' . ($validated['phone'] ?: 'Nao informado'),
            "Assunto: {$subjectLabel}",
            '',
            'Mensagem:',
            $validated['message'],
        ];

        try {
            Mail::raw(implode(PHP_EOL, $bodyLines), function ($message) use ($recipient, $recipientName, $fromAddress, $fromName, $mailSubject, $validated): void {
                $message
                    ->to($recipient, $recipientName !== '' ? $recipientName : null)
                    ->from($fromAddress, $fromName)
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject($mailSubject);
            });
        } catch (\Throwable $exception) {
            Log::error('Erro ao enviar formulario de contato da landing.', [
                'error' => $exception->getMessage(),
                'email' => $validated['email'],
            ]);

            return back()
                ->withInput()
                ->withErrors(['contact' => 'Nao foi possivel enviar sua mensagem agora. Tente novamente em instantes.']);
        }

        return back()->with('contact_success', 'Mensagem enviada com sucesso. Nossa equipe retornara em breve.');
    }

    /**
     * Exibe a pagina de manual do sistema
     */
    public function manual()
    {
        return view('landing.manual');
    }

    /**
     * Processa o pre-cadastro (integracao com o PreRegisterController existente)
     */
    public function storePreRegister(Request $request)
    {
        $preRegisterController = new \App\Http\Controllers\PreRegisterController();

        return $preRegisterController->store($request);
    }

    /**
     * Retorna os dados de um plano especifico em JSON (para modal)
     */
    public function getPlan($id)
    {
        $plan = Plan::publiclyAvailable()->findOrFail($id);

        return response()->json([
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'formatted_price' => $plan->formatted_price,
            'periodicity' => $plan->periodicity === 'yearly' ? 'Faturamento anual' : 'Faturamento mensal',
            'features' => $plan->features ?? [],
            'trial_enabled' => $plan->hasCommercialTrial(),
            'trial_days' => $plan->trial_days,
        ]);
    }
}
