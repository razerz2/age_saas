<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PatientReportController extends Controller
{
    public function index()
    {
        return view('tenant.reports.patients.index');
    }

    public function data(Request $request)
    {
        $query = Patient::withCount('appointments');

        // Filtros
        if ($request->filled('date_from')) {
            $query->whereHas('appointments', function($q) use ($request) {
                $q->whereDate('starts_at', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('appointments', function($q) use ($request) {
                $q->whereDate('starts_at', '<=', $request->date_to);
            });
        }

        $patients = $query->orderBy('full_name')->get();

        $summary = [
            'total' => $patients->count(),
            'with_appointments' => $patients->where('appointments_count', '>', 0)->count(),
            'new_this_month' => $patients->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        // Gráfico radar (perfil do paciente)
        $ageGroups = [
            '0-18' => 0,
            '19-30' => 0,
            '31-50' => 0,
            '51-70' => 0,
            '70+' => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->birth_date) {
                $age = Carbon::parse($patient->birth_date)->age;
                if ($age <= 18) $ageGroups['0-18']++;
                elseif ($age <= 30) $ageGroups['19-30']++;
                elseif ($age <= 50) $ageGroups['31-50']++;
                elseif ($age <= 70) $ageGroups['51-70']++;
                else $ageGroups['70+']++;
            }
        }

        $table = $patients->map(function($patient) {
            return [
                'id' => $patient->id,
                'name' => $patient->full_name,
                'email' => $patient->email ?? 'N/A',
                'phone' => $patient->phone ?? 'N/A',
                'appointments_count' => $patient->appointments_count ?? 0,
                'created_at' => $patient->created_at->format('d/m/Y'),
            ];
        });

        return response()->json([
            'summary' => $summary,
            'chart' => [
                'ageGroups' => $ageGroups,
            ],
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportPdf(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportCsv(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
}

