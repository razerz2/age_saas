<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use App\Services\Network\NetworkDoctorAggregatorService;
use Illuminate\Http\Request;

class NetworkDoctorController extends Controller
{
    protected NetworkDoctorAggregatorService $aggregatorService;

    public function __construct(NetworkDoctorAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Lista médicos da rede (somente leitura)
     */
    public function index(Request $request)
    {
        $network = app('currentNetwork');

        // Filtros
        $filters = [];
        if ($request->has('specialty') && $request->specialty) {
            $filters['specialty'] = $request->specialty;
        }
        if ($request->has('city') && $request->city) {
            $filters['city'] = $request->city;
        }
        if ($request->has('state') && $request->state) {
            $filters['state'] = $request->state;
        }
        if ($request->has('tenant_slug') && $request->tenant_slug) {
            $filters['tenant_slug'] = $request->tenant_slug;
        }

        // Busca médicos agregados
        $doctors = $this->aggregatorService->aggregateDoctors($network, $filters);

        // Busca especialidades para filtro
        $specialties = $this->aggregatorService->getNetworkSpecialties($network);

        // Cidades e estados únicos
        $cities = $doctors->pluck('city')->filter()->unique()->sort()->values();
        $states = $doctors->pluck('state')->filter()->unique()->sort()->values();

        // Tenants para filtro
        $tenants = $network->tenants()->where('status', 'active')->get(['id', 'subdomain', 'trade_name', 'legal_name']);

        return view('network-admin.doctors.index', [
            'network' => $network,
            'doctors' => $doctors,
            'specialties' => $specialties,
            'cities' => $cities,
            'states' => $states,
            'tenants' => $tenants,
            'filters' => $filters,
        ]);
    }
}

