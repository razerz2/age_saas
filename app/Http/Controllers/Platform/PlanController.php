<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'periodicity' => 'required|in:monthly,yearly',
            'period_months' => 'required|integer|min:1|max:12',
            'price_cents' => 'required|numeric|min:0',
            'features_json' => 'nullable|string',
            'is_active' => 'boolean',
        ]);


        // Converte reais para centavos
        $data['price_cents'] = (int) round($data['price_cents'] * 100);

        $data['features'] = array_filter(preg_split('/\r\n|\r|\n/', $data['features_json'] ?? ''));
        unset($data['features_json']);

        $data['is_active'] = $request->has('is_active');

        Plan::create($data);

        return redirect()->route('Platform.plans.index')->with('success', 'Plano criado com sucesso!');
    }


    public function show(Plan $plan)
    {
        return view('platform.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('platform.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'periodicity' => 'required|in:monthly,yearly',
            'period_months' => 'required|integer|min:1|max:12',
            'price_cents' => 'required|numeric|min:0',
            'features_json' => 'nullable|string',
            'is_active' => 'boolean',
        ]);


        $data['features'] = array_filter(preg_split('/\r\n|\r|\n/', $data['features_json'] ?? ''));
        unset($data['features_json']);
        $data['price_cents'] = (int) round($data['price_cents'] * 100);
        $data['is_active'] = $request->has('is_active');

        $plan->update($data);

        return redirect()->route('Platform.plans.index')->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('Platform.plans.index')->with('success', 'Plano excluído com sucesso!');
    }
}
