<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Models\Platform\Pais;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Exibe a página principal da landing
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->where('category', Plan::CATEGORY_COMMERCIAL)
            ->get();
        
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
        // Busca apenas planos comerciais ativos, ordenados por preço (menor para maior)
        $plans = Plan::where('is_active', true)
            ->where('category', Plan::CATEGORY_COMMERCIAL)
            ->orderBy('price_cents', 'asc')
            ->get();
        
        // Busca o ID do Brasil para o pré-cadastro
        $brazil = Pais::where('nome', 'Brasil')->first();
        $brazilId = $brazil ? $brazil->id_pais : 31; // 31 é o ID padrão no seeder
        
        return view('landing.plans', compact('plans', 'brazilId'));
    }

    /**
     * Exibe a página de contato
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Exibe a página de manual do sistema
     */
    public function manual()
    {
        return view('landing.manual');
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

    /**
     * Retorna os dados de um plano específico em JSON (para modal)
     */
    public function getPlan($id)
    {
        $plan = Plan::where('is_active', true)
            ->where('category', Plan::CATEGORY_COMMERCIAL)
            ->findOrFail($id);
        
        return response()->json([
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'formatted_price' => $plan->formatted_price,
            'periodicity' => $plan->periodicity === 'yearly' ? 'Faturamento anual' : 'Faturamento mensal',
            'features' => $plan->features ?? [],
        ]);
    }
}
