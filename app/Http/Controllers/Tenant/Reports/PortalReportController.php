<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PatientLogin;
use App\Models\Tenant\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PortalReportController extends Controller
{
    public function index()
    {
        return view('tenant.reports.portal.index');
    }

    public function data(Request $request)
    {
        // Relatório de uso do portal do paciente
        $query = PatientLogin::with('patient');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logins = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_logins' => $logins->count(),
            'active' => $logins->where('is_active', true)->count(),
        ];

        $table = $logins->map(function($login) {
            return [
                'id' => $login->id,
                'patient' => $login->patient->full_name ?? 'N/A',
                'email' => $login->email,
                'is_active' => $login->is_active,
                'created_at' => $login->created_at->format('d/m/Y'),
            ];
        });

        return response()->json([
            'summary' => $summary,
            'chart' => [],
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportPdf(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportCsv(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
}

