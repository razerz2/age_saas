<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\RecurringAppointment;
use Illuminate\Http\Request;

class RecurringReportController extends Controller
{
    use HasDoctorFilter;

    public function index()
    {
        return view('tenant.reports.recurring.index');
    }

    public function data(Request $request)
    {
        $query = RecurringAppointment::with(['doctor.user']);

        $this->applyDoctorFilter($query, 'doctor_id');

        $recurring = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $recurring->count(),
            'active' => $recurring->where('active', true)->count(),
        ];

        $table = $recurring->map(function($item) {
            return [
                'id' => $item->id,
                'doctor' => $item->doctor->user->name ?? 'N/A',
                'created_at' => $item->created_at->format('d/m/Y'),
                'is_active' => $item->active ?? false,
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

