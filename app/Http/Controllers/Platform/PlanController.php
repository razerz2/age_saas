<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Http\Requests\Platform\PlanRequest;

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
        $data = $request->validated();

        if ($request->filled('features_json')) {
            $data['features'] = array_filter(
                preg_split('/\r\n|\r|\n/', $request->features_json)
            );
        }

        $data['is_active'] = $request->has('is_active');
        $data['show_on_landing_page'] = $request->has('show_on_landing_page');
        $data['plan_type'] = $data['plan_type'] ?? Plan::TYPE_REAL;
        $data = $this->normalizeTrialSettings($data);

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
        $data = $request->validated();

        if ($request->filled('features_json')) {
            $data['features'] = array_filter(
                preg_split('/\r\n|\r|\n/', $request->features_json)
            );
        }

        $data['is_active'] = $request->has('is_active');
        $data['show_on_landing_page'] = $request->has('show_on_landing_page');
        $data['plan_type'] = $data['plan_type'] ?? Plan::TYPE_REAL;
        $data = $this->normalizeTrialSettings($data);

        $plan->update($data);

        return redirect()
            ->route('Platform.plans.index')
            ->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('Platform.plans.index')->with('success', 'Plano excluido com sucesso!');
    }

    private function normalizeTrialSettings(array $data): array
    {
        $isRealPlan = ($data['plan_type'] ?? Plan::TYPE_REAL) === Plan::TYPE_REAL;
        $trialEnabled = $isRealPlan && ! empty($data['trial_enabled']);

        $data['trial_enabled'] = $trialEnabled;
        $data['trial_days'] = $trialEnabled
            ? (int) ($data['trial_days'] ?? 0)
            : null;

        return $data;
    }
}
