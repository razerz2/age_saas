<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorReportController extends Controller
{
    use HasDoctorFilter;

    public function index()
    {
        return view('tenant.reports.doctors.index');
    }

    public function data(Request $request)
    {
        $query = Doctor::with(['user', 'specialties']);

        $this->applyDoctorFilter($query);

        $doctors = $query->orderBy('id')->get();
        
        // Adicionar contagem de appointments manualmente
        $doctors->each(function($doctor) use ($request) {
            $appointmentsQuery = $doctor->appointments();
            
            if ($request->filled('date_from')) {
                $appointmentsQuery->whereDate('starts_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $appointmentsQuery->whereDate('starts_at', '<=', $request->date_to);
            }
            
            $doctor->appointments_count = $appointmentsQuery->count();
        });

        $summary = [
            'total' => $doctors->count(),
            'active' => $doctors->filter(function($doctor) {
                return $doctor->user && $doctor->user->status === 'active';
            })->count(),
            'total_appointments' => $doctors->sum('appointments_count'),
        ];

        // Gráfico de barras por médico
        $byDoctor = $doctors->mapWithKeys(function($doctor) {
            return [$doctor->user->name ?? 'N/A' => $doctor->appointments_count ?? 0];
        });

        $table = $doctors->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user->name ?? 'N/A',
                'specialties' => $doctor->specialties->pluck('name')->join(', ') ?: 'N/A',
                'appointments_count' => $doctor->appointments_count ?? 0,
            ];
        });

        return response()->json([
            'summary' => $summary,
            'chart' => [
                'byDoctor' => $byDoctor,
            ],
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportPdf(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportCsv(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
}

