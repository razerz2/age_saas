<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\DoctorCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Dashboard financeiro principal
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Query base para transações
        $transactionsQuery = FinancialTransaction::query();
        $this->applyDoctorFilter($transactionsQuery, 'doctor_id');

        // Receita do dia
        $dailyIncome = (clone $transactionsQuery)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->whereDate('date', $now->toDateString())
            ->sum('amount');

        // Receita do mês
        $monthlyIncome = (clone $transactionsQuery)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Despesas do mês
        $monthlyExpense = (clone $transactionsQuery)
            ->where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Saldo atual (receitas - despesas pagas)
        $currentBalance = (clone $transactionsQuery)
            ->where('status', 'paid')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        // Cobranças pendentes
        $chargesQuery = FinancialCharge::query();
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $chargesQuery->whereHas('appointment', function($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                $chargesQuery->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!empty($allowedDoctorIds)) {
                $chargesQuery->whereHas('appointment', function($q) use ($allowedDoctorIds) {
                    $q->whereIn('doctor_id', $allowedDoctorIds);
                });
            } else {
                $chargesQuery->whereRaw('1 = 0');
            }
        }

        $pendingCharges = (clone $chargesQuery)
            ->where('status', 'pending')
            ->sum('amount');

        // Comissões pendentes
        $commissionsQuery = DoctorCommission::query();
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $commissionsQuery->where('doctor_id', $doctor->id);
            } else {
                $commissionsQuery->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $commissionsQuery->whereRaw('1 = 0'); // User não vê comissões
        }

        $pendingCommissions = (clone $commissionsQuery)
            ->where('status', 'pending')
            ->sum('amount');

        // Gráfico: Receitas últimos 12 meses
        $monthlyIncomeData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $income = (clone $transactionsQuery)
                ->where('type', 'income')
                ->where('status', 'paid')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $monthlyIncomeData[] = [
                'month' => $month->format('M/Y'),
                'value' => $income
            ];
        }

        // Gráfico: Receitas por categoria
        $incomeByCategory = (clone $transactionsQuery)
            ->where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->join('financial_categories', 'financial_transactions.category_id', '=', 'financial_categories.id')
            ->select('financial_categories.name', DB::raw('SUM(financial_transactions.amount) as total'))
            ->groupBy('financial_categories.id', 'financial_categories.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('tenant.finance.reports.index', compact(
            'dailyIncome',
            'monthlyIncome',
            'monthlyExpense',
            'currentBalance',
            'pendingCharges',
            'pendingCommissions',
            'monthlyIncomeData',
            'incomeByCategory'
        ));
    }
}

