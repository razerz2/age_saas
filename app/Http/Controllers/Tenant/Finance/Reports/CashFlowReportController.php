<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialAccount;
use App\Models\Tenant\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashFlowReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Exibe página do relatório de fluxo de caixa
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $accounts = FinancialAccount::where('active', true)->orderBy('name')->get();
        
        $doctorsQuery = Doctor::with('user')->whereHas('user', function($q) {
            $q->where('status', 'active');
        });
        $this->applyDoctorFilter($doctorsQuery);
        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.finance.reports.cashflow', compact('accounts', 'doctors'));
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

        $query = FinancialTransaction::with(['account', 'category', 'doctor.user'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        // Filtros opcionais
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $transactions = $query->orderBy('date')->orderBy('created_at')->get();

        // Calcular saldo acumulado
        $balance = 0;
        $data = [];
        foreach ($transactions as $transaction) {
            if ($transaction->type === 'income' && $transaction->status === 'paid') {
                $balance += $transaction->amount;
            } elseif ($transaction->type === 'expense' && $transaction->status === 'paid') {
                $balance -= $transaction->amount;
            }

            $data[] = [
                'date' => $transaction->date->format('d/m/Y'),
                'type' => $transaction->type === 'income' ? 'Receita' : 'Despesa',
                'category' => $transaction->category->name ?? 'N/A',
                'account' => $transaction->account->name ?? 'N/A',
                'amount' => number_format($transaction->amount, 2, ',', '.'),
                'balance' => number_format($balance, 2, ',', '.'),
                'status' => $transaction->status,
            ];
        }

        return response()->json([
            'data' => $data,
            'summary' => [
                'total_income' => $transactions->where('type', 'income')->where('status', 'paid')->sum('amount'),
                'total_expense' => $transactions->where('type', 'expense')->where('status', 'paid')->sum('amount'),
                'final_balance' => $balance,
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

        $query = FinancialTransaction::with(['account', 'category', 'doctor.user'])
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyDoctorFilter($query, 'doctor_id');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $transactions = $query->orderBy('date')->orderBy('created_at')->get();

        $filename = 'fluxo_caixa_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($transactions, $filename);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($transactions, $filename, $startDate, $endDate);
        }

        abort(400, 'Formato não suportado.');
    }

    protected function exportCsv($transactions, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, ['Data', 'Tipo', 'Categoria', 'Conta', 'Valor', 'Status'], ';');
            
            // Dados
            $balance = 0;
            foreach ($transactions as $transaction) {
                if ($transaction->type === 'income' && $transaction->status === 'paid') {
                    $balance += $transaction->amount;
                } elseif ($transaction->type === 'expense' && $transaction->status === 'paid') {
                    $balance -= $transaction->amount;
                }

                fputcsv($file, [
                    $transaction->date->format('d/m/Y'),
                    $transaction->type === 'income' ? 'Receita' : 'Despesa',
                    $transaction->category->name ?? 'N/A',
                    $transaction->account->name ?? 'N/A',
                    number_format($transaction->amount, 2, ',', '.'),
                    $transaction->status,
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPdf($transactions, $filename, $startDate, $endDate)
    {
        // PDF não implementado - requer DomPDF ou Snappy
        // Por enquanto, redireciona para CSV
        abort(400, 'Exportação PDF não está disponível. Use CSV.');
    }
}

