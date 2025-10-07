<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Platform.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $tenants = Tenant::orderBy('trade_name')->get();
        $plans = Plan::orderBy('name')->get();

        return view('Platform.subscriptions.create', compact('tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'starts_at' => 'required|date',
            'due_day' => 'required|integer|min:1|max:28',
            'status' => 'required|in:active,past_due,canceled,trialing',
            'auto_renew' => 'boolean',
        ]);

        // ⚠️ Verifica se o tenant já possui uma assinatura ativa ou trial
        $exists = Subscription::where('tenant_id', $data['tenant_id'])
            ->whereIn('status', ['active', 'trialing'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['general' => 'Este tenant já possui uma assinatura ativa ou em teste.'])
                ->withInput();
        }

        // Calcula data de término baseada no plano
        $plan = Plan::findOrFail($data['plan_id']);
        $data['ends_at'] = Carbon::parse($data['starts_at'])->addMonths($plan->period_months);
        $data['auto_renew'] = $request->has('auto_renew');

        Subscription::create($data);

        return redirect()->route('Platform.subscriptions.index')
            ->with('success', 'Assinatura criada com sucesso!');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);
        return view('Platform.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $tenants = Tenant::orderBy('trade_name')->get();
        $plans = Plan::orderBy('name')->get();

        return view('Platform.subscriptions.edit', compact('subscription', 'tenants', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'due_day' => 'required|integer|min:1|max:28',
            'status' => 'required|in:active,past_due,canceled,trialing',
            'auto_renew' => 'boolean',
        ]);

        $data['auto_renew'] = $request->has('auto_renew');

        $subscription->update($data);

        return redirect()->route('Platform.subscriptions.index')->with('success', 'Assinatura atualizada com sucesso!');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('Platform.subscriptions.index')->with('success', 'Assinatura excluída com sucesso!');
    }
}
