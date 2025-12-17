<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FinancialAccount;
use App\Models\Tenant\FinancialCategory;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialCharge;
use App\Models\Tenant\DoctorCommission;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /**
     * Dashboard financeiro
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        // Estatísticas básicas
        $totalIncome = FinancialTransaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $totalExpense = FinancialTransaction::where('type', 'expense')
            ->where('status', 'paid')
            ->sum('amount');

        $pendingCharges = FinancialCharge::where('status', 'pending')->count();
        $overdueCharges = FinancialCharge::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();

        $recentTransactions = FinancialTransaction::with(['account', 'category', 'patient', 'doctor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('tenant.finance.index', compact(
            'totalIncome',
            'totalExpense',
            'pendingCharges',
            'overdueCharges',
            'recentTransactions'
        ));
    }
}

