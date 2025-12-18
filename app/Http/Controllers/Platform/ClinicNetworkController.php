<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClinicNetworkController extends Controller
{
    /**
     * Lista todas as redes
     */
    public function index()
    {
        $networks = ClinicNetwork::withCount('tenants')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.clinic-networks.index', compact('networks'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('platform.clinic-networks.create');
    }

    /**
     * Salva nova rede
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:clinic_networks,slug'],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        ClinicNetwork::create($validated);

        return redirect()
            ->route('Platform.clinic-networks.index')
            ->with('success', 'Rede de clínicas criada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit(ClinicNetwork $network)
    {
        $network->load('tenants');
        
        // Busca tenants disponíveis (sem rede ou de outras redes)
        $availableTenants = Tenant::where(function ($query) use ($network) {
                $query->whereNull('network_id')
                    ->orWhere('network_id', '!=', $network->id);
            })
            ->where('status', 'active')
            ->orderBy('trade_name')
            ->orderBy('legal_name')
            ->get();

        return view('platform.clinic-networks.edit', compact('network', 'availableTenants'));
    }

    /**
     * Atualiza rede
     */
    public function update(Request $request, ClinicNetwork $network)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('clinic_networks')->ignore($network->id)],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $network->update($validated);

        return redirect()
            ->route('Platform.clinic-networks.index')
            ->with('success', 'Rede de clínicas atualizada com sucesso!');
    }

    /**
     * Remove rede (opcional - melhor usar inativar)
     */
    public function destroy(ClinicNetwork $network)
    {
        // Remove vínculos antes de deletar
        $network->tenants()->update(['network_id' => null]);
        
        $network->delete();

        return redirect()
            ->route('Platform.clinic-networks.index')
            ->with('success', 'Rede de clínicas removida com sucesso!');
    }

    /**
     * Vincula tenant à rede
     */
    public function attachTenant(Request $request, ClinicNetwork $network)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
        ]);

        $tenant = Tenant::where(function ($query) use ($network) {
                $query->whereNull('network_id')
                    ->orWhere('network_id', '!=', $network->id);
            })
            ->findOrFail($validated['tenant_id']);

        $tenant->update(['network_id' => $network->id]);

        return back()->with('success', 'Clínica vinculada à rede com sucesso!');
    }

    /**
     * Remove tenant da rede
     */
    public function detachTenant(ClinicNetwork $network, Tenant $tenant)
    {
        abort_if($tenant->network_id !== $network->id, 403, 'Esta clínica não pertence a esta rede.');

        $tenant->update(['network_id' => null]);

        return back()->with('success', 'Clínica removida da rede com sucesso!');
    }
}

