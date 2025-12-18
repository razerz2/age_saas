<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use App\Services\Network\NetworkAppointmentStatsService;
use Illuminate\Http\Request;

class NetworkAppointmentController extends Controller
{
    protected NetworkAppointmentStatsService $statsService;

    public function __construct(NetworkAppointmentStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Métricas agregadas de agendamentos (somente leitura)
     */
    public function index(Request $request)
    {
        $network = app('currentNetwork');

        // Filtros de período
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Busca estatísticas
        $stats = $this->statsService->getStats($network, $startDate, $endDate);

        return view('network-admin.appointments.index', [
            'network' => $network,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}

