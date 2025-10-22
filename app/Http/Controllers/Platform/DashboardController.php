<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\Subscription;
use App\Models\Platform\Invoices;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // 1) Tenants ativos
        $activeTenants = Tenant::where('status', 'active')->count();

        // 2) Assinaturas ativas
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // 3) Faturamento do mês (faturas pagas no mês/ano atual)
        $monthlyRevenueCents = Invoices::where('status', 'paid')
            ->whereYear('due_date', $now->year)
            ->whereMonth('due_date', $now->month)
            ->sum('amount_cents');

        $monthlyRevenue = $monthlyRevenueCents / 100;
        // converte centavos em reais

        // 4) Assinaturas canceladas no mês/ano atual
        $cancelledSubscriptions = Subscription::where('status', 'cancelled')
            ->whereYear('updated_at', $now->year)
            ->whereMonth('updated_at', $now->month)
            ->count();

        // Dados para gráfico de “Receita total x Faturas vencidas” no formato doughnut
        $totalReceivedCents = Invoices::where('status', 'paid')->sum('amount_cents');
        $totalOverdueCents  = Invoices::where('status', 'overdue')->sum('amount_cents');

        $totalReceived = $totalReceivedCents / 100;
        $totalOverdue  = $totalOverdueCents / 100;

        // Crescimento de clientes (mês a mês - ano atual)
        $clientsGrowth = \App\Models\Platform\Tenant::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $now->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => $clientsGrowth[$m] ?? 0]); // Preenche meses vazios com 0

        // === TOP 5 TENANTS MAIS ANTIGOS ===
        $oldestTenants = Tenant::with('localizacao.estado')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        return view('platform.dashboard', compact(
            'now',
            'activeTenants',
            'activeSubscriptions',
            'monthlyRevenue',
            'cancelledSubscriptions',
            'totalReceived',
            'totalOverdue',
            'months',
            'oldestTenants'
        ));
    }
}
