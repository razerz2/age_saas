<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentsReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Exibe página do relatório de pagamentos recebidos
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        return view('tenant.finance.reports.payments');
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

        $query = FinancialCharge::with(['patient', 'appointment.doctor.user', 'transaction'])
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate]);

        // Filtrar por médico
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->whereHas('appointment', function($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!empty($allowedDoctorIds)) {
                $query->whereHas('appointment', function($q) use ($allowedDoctorIds) {
                    $q->whereIn('doctor_id', $allowedDoctorIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $payments = $query->orderBy('updated_at', 'desc')->get();

        $data = $payments->map(function($charge) {
            return [
                'patient' => $charge->patient->full_name ?? 'N/A',
                'amount' => number_format($charge->amount, 2, ',', '.'),
                'payment_method' => 'PIX', // TODO: obter do Asaas ou transaction
                'payment_date' => $charge->updated_at->format('d/m/Y H:i'),
                'appointment' => $charge->appointment 
                    ? $charge->appointment->starts_at->format('d/m/Y H:i') 
                    : 'N/A',
                'doctor' => $charge->appointment && $charge->appointment->doctor
                    ? $charge->appointment->doctor->user->name ?? 'N/A'
                    : 'N/A',
            ];
        });

        return response()->json([
            'data' => $data,
            'summary' => [
                'total' => $payments->sum('amount'),
                'count' => $payments->count(),
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

        $query = FinancialCharge::with(['patient', 'appointment.doctor.user'])
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->whereHas('appointment', function($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!empty($allowedDoctorIds)) {
                $query->whereHas('appointment', function($q) use ($allowedDoctorIds) {
                    $q->whereIn('doctor_id', $allowedDoctorIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $payments = $query->orderBy('updated_at', 'desc')->get();

        $filename = 'pagamentos_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($payments, $filename);
        }

        abort(400, 'Formato não suportado.');
    }

    protected function exportCsv($payments, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Paciente', 'Valor Pago', 'Método', 'Data Pagamento', 'Agendamento', 'Médico'], ';');
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->patient->full_name ?? 'N/A',
                    number_format($payment->amount, 2, ',', '.'),
                    'PIX', // TODO: obter método real
                    $payment->updated_at->format('d/m/Y H:i'),
                    $payment->appointment ? $payment->appointment->starts_at->format('d/m/Y H:i') : 'N/A',
                    $payment->appointment && $payment->appointment->doctor 
                        ? ($payment->appointment->doctor->user->name ?? 'N/A')
                        : 'N/A',
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

