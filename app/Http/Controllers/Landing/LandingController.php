<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Exibe a página principal da landing
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)->get();
        
        return view('landing.index', compact('plans'));
    }

    /**
     * Exibe a página de funcionalidades detalhadas
     */
    public function features()
    {
        return view('landing.features');
    }

    /**
     * Exibe a página de planos
     */
    public function plans()
    {
        $plans = Plan::where('is_active', true)->get();
        
        return view('landing.plans', compact('plans'));
    }

    /**
     * Exibe a página de contato
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Processa o pré-cadastro (integração com o PreRegisterController existente)
     */
    public function storePreRegister(Request $request)
    {
        // Redireciona para o controller de pré-cadastro existente
        $preRegisterController = new \App\Http\Controllers\PreRegisterController();
        return $preRegisterController->store($request);
    }
}
