<?php

namespace App\Http\Controllers\Tenant\Finance\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChargesReportController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Exibe página do relatório de cobranças
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        return view('tenant.finance.reports.charges');
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

        $query = FinancialCharge::with(['patient', 'appointment.doctor.user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origin')) {
            $query->where('origin', $request->origin);
        }

        $charges = $query->orderBy('created_at', 'desc')->get();

        $data = $charges->map(function($charge) {
            return [
                'id' => substr($charge->id, 0, 8),
                'patient' => $charge->patient->full_name ?? 'N/A',
                'appointment' => $charge->appointment 
                    ? $charge->appointment->starts_at->format('d/m/Y H:i') 
                    : 'N/A',
                'doctor' => $charge->appointment && $charge->appointment->doctor
                    ? $charge->appointment->doctor->user->name ?? 'N/A'
                    : 'N/A',
                'amount' => number_format($charge->amount, 2, ',', '.'),
                'status' => $charge->status,
                'origin' => $charge->origin,
                'due_date' => $charge->due_date->format('d/m/Y'),
                'created_at' => $charge->created_at->format('d/m/Y H:i'),
                'payment_link' => $charge->payment_link,
            ];
        });

        return response()->json([
            'data' => $data,
            'summary' => [
                'total' => $charges->sum('amount'),
                'paid' => $charges->where('status', 'paid')->sum('amount'),
                'pending' => $charges->where('status', 'pending')->sum('amount'),
                'cancelled' => $charges->where('status', 'cancelled')->sum('amount'),
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
            ->whereBetween('created_at', [$startDate, $endDate]);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origin')) {
            $query->where('origin', $request->origin);
        }

        $charges = $query->orderBy('created_at', 'desc')->get();

        $filename = 'cobrancas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($charges, $filename);
        }

        abort(400, 'Formato não suportado.');
    }

    protected function exportCsv($charges, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($charges) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Paciente', 'Agendamento', 'Médico', 'Valor', 'Status', 'Origem', 'Vencimento', 'Criado em'], ';');
            
            foreach ($charges as $charge) {
                fputcsv($file, [
                    $charge->patient->full_name ?? 'N/A',
                    $charge->appointment ? $charge->appointment->starts_at->format('d/m/Y H:i') : 'N/A',
                    $charge->appointment && $charge->appointment->doctor 
                        ? ($charge->appointment->doctor->user->name ?? 'N/A')
                        : 'N/A',
                    number_format($charge->amount, 2, ',', '.'),
                    $charge->status,
                    $charge->origin,
                    $charge->due_date->format('d/m/Y'),
                    $charge->created_at->format('d/m/Y H:i'),
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

