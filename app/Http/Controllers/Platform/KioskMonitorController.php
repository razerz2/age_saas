<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use Illuminate\Support\Facades\DB;

class KioskMonitorController extends Controller
{
    public function index()
    {
        return view('platform.kiosk.monitor');
    }

    public function data()
    {
        $totalClientes = Tenant::where('status', 'active')->count();

        $totalAssinaturas = Subscription::where('status', 'active')->count();

        // Faturamento em CENTAVOS → divide por 100
        $faturamentoRaw = DB::table('invoices')
            ->where('status', 'paid')
            ->sum('amount_cents');

        $faturamento = $faturamentoRaw / 100;

        return response()->json([
            'clientes'     => $totalClientes,
            'assinaturas'  => $totalAssinaturas,
            'faturamento'  => number_format($faturamento, 2, ',', '.'),
        ]);
    }
}
