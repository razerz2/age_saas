<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Network\NetworkDoctorAggregatorService;
use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;

class NetworkPublicController extends Controller
{
    protected NetworkDoctorAggregatorService $aggregatorService;

    public function __construct(NetworkDoctorAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Página inicial da rede
     * Exibe informações institucionais e unidades
     */
    public function home()
    {
        $network = app('currentNetwork');

        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->with('localizacao.cidade', 'localizacao.estado')
            ->get();

        return view('network.home', [
            'network' => $network,
            'units' => $tenants,
        ]);
    }

    /**
     * Lista de médicos da rede
     * Permite filtrar por especialidade e localidade
     */
    public function doctors(Request $request)
    {
        $network = app('currentNetwork');

        // Prepara filtros
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

        // Busca médicos agregados
        $doctors = $this->aggregatorService->aggregateDoctors($network, $filters);

        // Busca especialidades da rede para o filtro
        $specialties = $this->aggregatorService->getNetworkSpecialties($network);

        // Extrai cidades e estados únicos para filtros
        $cities = $doctors->pluck('city')->filter()->unique()->sort()->values();
        $states = $doctors->pluck('state')->filter()->unique()->sort()->values();

        return view('network.doctors', [
            'network' => $network,
            'doctors' => $doctors,
            'specialties' => $specialties,
            'cities' => $cities,
            'states' => $states,
            'filters' => $filters,
        ]);
    }

    /**
     * Lista de unidades da rede
     */
    public function units()
    {
        $network = app('currentNetwork');

        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->with('localizacao.cidade', 'localizacao.estado')
            ->get();

        return view('network.units', [
            'network' => $network,
            'units' => $tenants,
        ]);
    }

    /**
     * Redireciona para agendamento no tenant correto
     * Este método pode ser usado como rota alternativa ao redirect direto nas views
     */
    public function redirectToAppointment(Request $request)
    {
        $tenantSlug = $request->get('tenant_slug');
        $doctorId = $request->get('doctor');

        if (!$tenantSlug) {
            abort(400, 'Tenant slug é obrigatório');
        }

        // Redireciona para a rota EXISTENTE de agendamento do tenant
        $url = route('public.appointment.create', [
            'slug' => $tenantSlug,
            'doctor' => $doctorId,
        ]);

        return redirect($url);
    }
}

