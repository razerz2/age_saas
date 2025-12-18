<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use App\Services\Network\NetworkFinanceStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NetworkFinanceController extends Controller
{
    protected NetworkFinanceStatsService $statsService;

    public function __construct(NetworkFinanceStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Indicadores financeiros agregados (somente leitura)
     * Apenas usuários com role 'admin' ou 'finance' podem acessar
     */
    public function index(Request $request)
    {
        $network = app('currentNetwork');
        $user = auth()->guard('network')->user();

        // Verifica permissão
        if (!$user->canViewFinance()) {
            abort(403, 'Acesso negado. Você não tem permissão para visualizar dados financeiros.');
        }

        // Filtros de período
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Busca estatísticas financeiras
        $stats = $this->statsService->getStats($network, $startDate, $endDate);

        return view('network-admin.finance.index', [
            'network' => $network,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}

