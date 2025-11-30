<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormResponse;
use Illuminate\Http\Request;

class FormReportController extends Controller
{
    public function index()
    {
        return view('tenant.reports.forms.index');
    }

    public function data(Request $request)
    {
        $query = Form::withCount('responses');

        if ($request->filled('date_from')) {
            $query->whereHas('responses', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('responses', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        $forms = $query->orderBy('name')->get();

        $summary = [
            'total' => $forms->count(),
            'total_responses' => $forms->sum('responses_count'),
        ];

        $table = $forms->map(function($form) {
            return [
                'id' => $form->id,
                'name' => $form->name,
                'responses_count' => $form->responses_count ?? 0,
                'created_at' => $form->created_at->format('d/m/Y'),
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

