<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\DoctorCommission;
use App\Models\Tenant\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommissionsReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Exibe página do relatório de comissões
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $doctors = null;
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'admin') {
            $doctors = Doctor::with('user')
                ->whereHas('user', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('id')
                ->get();
        }

        return view('tenant.finance.reports.commissions', compact('doctors'));
    }

    /**
     * Retorna dados do relatório (AJAX)
     */
    public function data(Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            return response()->json(['error' => 'Módulo financeiro não está habilitado.'], 403);
        }

        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        $query = DoctorCommission::with(['doctor.user', 'transaction.appointment'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $query->whereRaw('1 = 0'); // User não vê comissões
        }

        if ($request->filled('doctor_id') && $user->role === 'admin') {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $data = $commissions->map(function($commission) {
            return [
                'doctor' => $commission->doctor->user->name ?? 'N/A',
                'appointment' => $commission->transaction && $commission->transaction->appointment
                    ? $commission->transaction->appointment->starts_at->format('d/m/Y H:i')
                    : 'N/A',
                'amount' => number_format($commission->amount, 2, ',', '.'),
                'percentage' => number_format($commission->percentage, 2, ',', '.') . '%',
                'status' => $commission->status,
                'paid_at' => $commission->paid_at ? $commission->paid_at->format('d/m/Y H:i') : '-',
                'created_at' => $commission->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data' => $data,
            'summary' => [
                'total' => $commissions->sum('amount'),
                'paid' => $commissions->where('status', 'paid')->sum('amount'),
                'pending' => $commissions->where('status', 'pending')->sum('amount'),
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

        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        $query = DoctorCommission::with(['doctor.user', 'transaction.appointment'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('doctor_id') && $user->role === 'admin') {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'comissoes_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($commissions, $filename);
        }

        abort(400, 'Formato não suportado.');
    }

    protected function exportCsv($commissions, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Médico', 'Agendamento', 'Valor', 'Percentual', 'Status', 'Data Pagamento', 'Criado em'], ';');
            
            foreach ($commissions as $commission) {
                fputcsv($file, [
                    $commission->doctor->user->name ?? 'N/A',
                    $commission->transaction && $commission->transaction->appointment
                        ? $commission->transaction->appointment->starts_at->format('d/m/Y H:i')
                        : 'N/A',
                    number_format($commission->amount, 2, ',', '.'),
                    number_format($commission->percentage, 2, ',', '.') . '%',
                    $commission->status,
                    $commission->paid_at ? $commission->paid_at->format('d/m/Y H:i') : '-',
                    $commission->created_at->format('d/m/Y H:i'),
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

