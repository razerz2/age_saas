<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Http\Requests\PlanRequest;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('created_at', 'desc')->get();
        return view('platform.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('platform.plans.create');
    }

    public function store(PlanRequest $request)
    {
        // ✅ Dados validados via PlanRequest
        $data = $request->validated();

        // 🔹 Se o form envia preço em reais (ex: 89.90), converte para centavos
        //    Caso já venha em centavos, basta remover esta linha.
        $data['price_cents'] = (int) round($data['price_cents'] * 100);

        // 🔹 Converte o campo de texto "features_json" em array (se existir)
        if ($request->filled('features_json')) {
            $data['features'] = array_filter(
                preg_split('/\r\n|\r|\n/', $request->features_json)
            );
        }

        // 🔹 Garante boolean no campo is_active (checkbox)
        $data['is_active'] = $request->has('is_active');

        // 🔹 Cria o plano
        Plan::create($data);

        return redirect()
            ->route('Platform.plans.index')
            ->with('success', 'Plano criado com sucesso!');
    }


    public function show(Plan $plan)
    {
        return view('platform.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('platform.plans.edit', compact('plan'));
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        // ✅ Usa apenas os dados validados
        $data = $request->validated();

        // 🔹 Converte preço de reais → centavos (se necessário)
        $data['price_cents'] = (int) round($data['price_cents'] * 100);

        // 🔹 Converte texto de features (multilinha) para array
        if ($request->filled('features_json')) {
            $data['features'] = array_filter(
                preg_split('/\r\n|\r|\n/', $request->features_json)
            );
        }

        // 🔹 Garante boolean correto
        $data['is_active'] = $request->has('is_active');

        // 🔹 Atualiza o plano
        $plan->update($data);

        return redirect()
            ->route('Platform.plans.index')
            ->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('Platform.plans.index')->with('success', 'Plano excluído com sucesso!');
    }
}
