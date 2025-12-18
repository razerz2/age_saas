<?php

namespace App\Services\Network;

use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\FinancialTransaction;
use Carbon\Carbon;

class NetworkFinanceStatsService
{
    /**
     * Retorna estatísticas financeiras agregadas
     *
     * @param ClinicNetwork $network
     * @param string|null $startDate Data de início (Y-m-d)
     * @param string|null $endDate Data de fim (Y-m-d)
     * @return array
     */
    public function getStats(ClinicNetwork $network, ?string $startDate = null, ?string $endDate = null): array
    {
        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->get();

        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalCharges = 0;
        $paidCharges = 0;
        $byClinic = [];

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                // Receitas (transações de entrada)
                $revenue = FinancialTransaction::where('type', 'income')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->sum('amount');

                // Despesas (transações de saída)
                $expenses = FinancialTransaction::where('type', 'expense')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->sum('amount');

                // Cobranças
                $charges = FinancialCharge::whereBetween('due_date', [$startDate, $endDate])->get();
                $clinicCharges = $charges->count();
                $clinicPaidCharges = $charges->where('status', 'paid')->count();
                $clinicRevenue = $charges->where('status', 'paid')->sum('amount');

                $totalRevenue += $revenue + $clinicRevenue;
                $totalExpenses += abs($expenses); // Garante positivo
                $totalCharges += $clinicCharges;
                $paidCharges += $clinicPaidCharges;

                // Por clínica
                if ($revenue > 0 || $expenses > 0 || $clinicCharges > 0) {
                    $byClinic[] = [
                        'tenant_id' => $tenant->id,
                        'tenant_slug' => $tenant->subdomain,
                        'tenant_name' => $tenant->trade_name ?? $tenant->legal_name,
                        'revenue' => $revenue + $clinicRevenue,
                        'expenses' => abs($expenses),
                        'charges_count' => $clinicCharges,
                        'paid_charges_count' => $clinicPaidCharges,
                        'balance' => ($revenue + $clinicRevenue) - abs($expenses),
                    ];
                }

            } finally {
                tenancy()->end();
            }
        }

        // Calcula ticket médio
        $averageTicket = $paidCharges > 0 
            ? round($totalRevenue / $paidCharges, 2)
            : 0;

        // Ordena por receita (maior primeiro)
        usort($byClinic, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_expenses' => round($totalExpenses, 2),
            'balance' => round($totalRevenue - $totalExpenses, 2),
            'total_charges' => $totalCharges,
            'paid_charges' => $paidCharges,
            'average_ticket' => $averageTicket,
            'by_clinic' => $byClinic,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }
}

