<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Http\Request;

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
