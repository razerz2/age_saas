<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;

class NetworkClinicController extends Controller
{
    /**
     * Lista clínicas da rede (somente leitura)
     */
    public function index()
    {
        $network = app('currentNetwork');

        if (!$network) {
            abort(404, 'Rede de clínicas não encontrada');
        }

        // Busca todas as clínicas da rede (não apenas as ativas)
        // para permitir visualização de todas as unidades
        $clinics = Tenant::where('network_id', $network->id)
            ->with([
                'localizacao.cidade', 
                'localizacao.estado',
                'subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('ends_at')
                                ->orWhere('ends_at', '>', now());
                        })
                        ->latest('starts_at')
                        ->limit(1)
                        ->with('plan');
                }
            ])
            ->orderBy('trade_name')
            ->orderBy('legal_name')
            ->get();

        // Adiciona a assinatura ativa como atributo para cada clínica
        $clinics->each(function ($clinic) {
            $clinic->setAttribute('activeSubscription', $clinic->subscriptions->first());
        });

        // Log para debug (pode ser removido em produção)
        \Log::debug('NetworkClinicController::index', [
            'network_id' => $network->id,
            'network_name' => $network->name,
            'clinics_count' => $clinics->count(),
            'clinic_ids' => $clinics->pluck('id')->toArray(),
        ]);

        return view('network-admin.clinics.index', [
            'network' => $network,
            'clinics' => $clinics,
        ]);
    }

    /**
     * Exibe detalhes de uma clínica da rede
     */
    public function show($id)
    {
        $network = app('currentNetwork');

        if (!$network) {
            abort(404, 'Rede de clínicas não encontrada');
        }

        // Valida se o ID é um UUID válido
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            abort(404, 'Clínica não encontrada');
        }

        // Busca a clínica verificando se pertence à rede
        $clinic = Tenant::where('network_id', $network->id)
            ->with([
                'localizacao.cidade',
                'localizacao.estado',
                'localizacao.pais',
                'subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('ends_at')
                                ->orWhere('ends_at', '>', now());
                        })
                        ->latest('starts_at')
                        ->limit(1)
                        ->with('plan');
                }
            ])
            ->findOrFail($id);

        // Adiciona a assinatura ativa como atributo
        $clinic->setAttribute('activeSubscription', $clinic->subscriptions->first());

        return view('network-admin.clinics.show', [
            'network' => $network,
            'clinic' => $clinic,
        ]);
    }
}

