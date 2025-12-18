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

        $clinics = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->with(['localizacao.cidade', 'localizacao.estado', 'activeSubscription.plan'])
            ->orderBy('trade_name')
            ->orderBy('legal_name')
            ->get();

        return view('network-admin.clinics.index', [
            'network' => $network,
            'clinics' => $clinics,
        ]);
    }
}

