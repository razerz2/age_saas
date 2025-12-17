<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncomeExpenseReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Exibe página do relatório de receitas x despesas
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        return view('tenant.finance.reports.income_expense');
    }

    /**
     * Retorna dados do relatório (AJAX)
     */
    public function data(Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            return response()->json(['error' => 'Módulo financeiro não está habilitado.'], 403);
        }

        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));
        $groupBy = $request->input('group_by', 'day'); // day ou month

        $query = FinancialTransaction::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'paid');

        $this->applyDoctorFilter($query, 'doctor_id');

        if ($groupBy === 'day') {
            $income = (clone $query)
                ->where('type', 'income')
                ->selectRaw('DATE(date) as period, SUM(amount) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period')
                ->mapWithKeys(fn($total, $period) => [Carbon::parse($period)->format('d/m/Y') => $total]);

            $expense = (clone $query)
                ->where('type', 'expense')
                ->selectRaw('DATE(date) as period, SUM(amount) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period')
                ->mapWithKeys(fn($total, $period) => [Carbon::parse($period)->format('d/m/Y') => $total]);
        } else {
            $income = (clone $query)
                ->where('type', 'income')
                ->selectRaw('DATE_FORMAT(date, "%Y-%m") as period, SUM(amount) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period')
                ->mapWithKeys(fn($total, $period) => [Carbon::createFromFormat('Y-m', $period)->format('M/Y') => $total]);

            $expense = (clone $query)
                ->where('type', 'expense')
                ->selectRaw('DATE_FORMAT(date, "%Y-%m") as period, SUM(amount) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period')
                ->mapWithKeys(fn($total, $period) => [Carbon::createFromFormat('Y-m', $period)->format('M/Y') => $total]);
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netResult = $totalIncome - $totalExpense;

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_result' => $netResult,
            ]
        ]);
    }

    /**
     * Exporta relatório
     */
    public function export(string $format, Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));
        $groupBy = $request->input('group_by', 'day');

        $query = FinancialTransaction::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'paid');

        $this->applyDoctorFilter($query, 'doctor_id');

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netResult = $totalIncome - $totalExpense;

        $filename = 'receitas_despesas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($query, $groupBy, $filename, $totalIncome, $totalExpense, $netResult);
        }

        abort(400, 'Formato não suportado.');
    }

    protected function exportCsv($query, $groupBy, $filename, $totalIncome, $totalExpense, $netResult)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($query, $groupBy, $totalIncome, $totalExpense, $netResult) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Período', 'Receitas', 'Despesas', 'Resultado Líquido'], ';');
            
            if ($groupBy === 'day') {
                $data = (clone $query)
                    ->selectRaw('DATE(date) as period, 
                        SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income,
                        SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                $data = (clone $query)
                    ->selectRaw('DATE_FORMAT(date, "%Y-%m") as period,
                        SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income,
                        SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            foreach ($data as $row) {
                $period = $groupBy === 'day' 
                    ? Carbon::parse($row->period)->format('d/m/Y')
                    : Carbon::createFromFormat('Y-m', $row->period)->format('M/Y');
                
                fputcsv($file, [
                    $period,
                    number_format($row->income, 2, ',', '.'),
                    number_format($row->expense, 2, ',', '.'),
                    number_format($row->income - $row->expense, 2, ',', '.'),
                ], ';');
            }
            
            fputcsv($file, ['TOTAL', number_format($totalIncome, 2, ',', '.'), number_format($totalExpense, 2, ',', '.'), number_format($netResult, 2, ',', '.')], ';');
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

