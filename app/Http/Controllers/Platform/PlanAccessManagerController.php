<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlanAccessRule;
use App\Models\Platform\Plan;
use App\Models\Platform\SubscriptionFeature;
use App\Models\Platform\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlanAccessManagerController extends Controller
{
    /**
     * Lista regras de acesso por plano
     */
    public function index()
    {
        $rules = PlanAccessRule::with(['plan', 'features'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.subscription_access.index', compact('rules'));
    }

    /**
     * Formulário para criar nova regra
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $features = SubscriptionFeature::orderBy('label')->get();

        return view('platform.subscription_access.create', compact('plans', 'features'));
    }

    /**
     * Cria nova regra de acesso
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'max_admin_users' => 'required|integer|min:0',
            'max_common_users' => 'required|integer|min:0',
            'max_doctors' => 'required|integer|min:0',
            'features' => 'array',
            'features.*' => 'exists:subscription_features,id',
        ]);

        try {
            DB::beginTransaction();

            // Verifica se já existe regra para este plano
            $existingRule = PlanAccessRule::where('plan_id', $request->plan_id)->first();
            if ($existingRule) {
                return back()->withInput()->withErrors([
                    'plan_id' => 'Já existe uma regra de acesso para este plano. Use a edição para modificar.'
                ]);
            }

            // Cria a regra
            $rule = PlanAccessRule::create([
                'plan_id' => $request->plan_id,
                'max_admin_users' => $request->max_admin_users,
                'max_common_users' => $request->max_common_users,
                'max_doctors' => $request->max_doctors,
            ]);

            // Busca todas as features
            $allFeatures = SubscriptionFeature::all();
            $selectedFeatures = $request->features ?? [];

            // Associa features
            foreach ($allFeatures as $feature) {
                $allowed = false;

                // Se for default, sempre permite
                if ($feature->is_default) {
                    $allowed = true;
                } elseif (in_array($feature->id, $selectedFeatures)) {
                    $allowed = true;
                }

                $rule->features()->attach($feature->id, [
                    'allowed' => $allowed,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('Platform.subscription-access.index')
                ->with('success', 'Regra de acesso criada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Erro ao criar regra de acesso: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Erro ao criar regra de acesso. Verifique os logs.']);
        }
    }

    /**
     * Exibe detalhes da regra
     */
    public function show(PlanAccessRule $subscriptionAccess)
    {
        $rule = $subscriptionAccess->load(['plan', 'features']);

        return view('platform.subscription_access.show', compact('rule'));
    }

    /**
     * Formulário para editar regra
     */
    public function edit(PlanAccessRule $subscriptionAccess)
    {
        $rule = $subscriptionAccess->load(['features']);
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $features = SubscriptionFeature::orderBy('label')->get();

        return view('platform.subscription_access.edit', compact('rule', 'plans', 'features'));
    }

    /**
     * Atualiza regra de acesso
     */
    public function update(Request $request, PlanAccessRule $subscriptionAccess)
    {
        $request->validate([
            'max_admin_users' => 'required|integer|min:0',
            'max_common_users' => 'required|integer|min:0',
            'max_doctors' => 'required|integer|min:0',
            'features' => 'array',
            'features.*' => 'exists:subscription_features,id',
        ]);

        try {
            DB::beginTransaction();

            // Atualiza limites
            $subscriptionAccess->update([
                'max_admin_users' => $request->max_admin_users,
                'max_common_users' => $request->max_common_users,
                'max_doctors' => $request->max_doctors,
            ]);

            // Busca todas as features
            $allFeatures = SubscriptionFeature::all();
            $selectedFeatures = $request->features ?? [];

            // Remove todas as associações
            $subscriptionAccess->features()->detach();

            // Reassocia features
            foreach ($allFeatures as $feature) {
                $allowed = false;

                // Se for default, sempre permite
                if ($feature->is_default) {
                    $allowed = true;
                } elseif (in_array($feature->id, $selectedFeatures)) {
                    $allowed = true;
                }

                $subscriptionAccess->features()->attach($feature->id, [
                    'allowed' => $allowed,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('Platform.subscription-access.index')
                ->with('success', 'Regra de acesso atualizada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar regra de acesso: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Erro ao atualizar regra de acesso. Verifique os logs.']);
        }
    }

    /**
     * Exclui regra de acesso
     */
    public function destroy(PlanAccessRule $subscriptionAccess)
    {
        try {
            // Verifica se há assinaturas usando este plano
            $subscriptionsCount = Subscription::where('plan_id', $subscriptionAccess->plan_id)
                ->where('status', 'active')
                ->count();

            if ($subscriptionsCount > 0) {
                return back()->withErrors([
                    'general' => 'Não é possível excluir esta regra. Existem assinaturas ativas usando este plano.'
                ]);
            }

            $subscriptionAccess->delete();

            return redirect()
                ->route('Platform.subscription-access.index')
                ->with('success', 'Regra de acesso excluída com sucesso!');
        } catch (\Throwable $e) {
            Log::error("Erro ao excluir regra de acesso: {$e->getMessage()}");

            return back()->withErrors(['general' => 'Erro ao excluir regra de acesso.']);
        }
    }
}
