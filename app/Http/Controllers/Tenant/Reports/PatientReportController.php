<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PatientReportController extends Controller
{
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.patients.index');
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total' => (clone $query)->count('patients.id'),
            'with_appointments' => (clone $query)->has('appointments')->count('patients.id'),
            'new_this_month' => (clone $query)->where('patients.created_at', '>=', now()->startOfMonth())->count('patients.id'),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (Patient $patient) {
            return [
                'name' => e($patient->full_name ?? 'N/A'),
                'email' => e($patient->email ?? '-'),
                'phone' => e($patient->phone ?? '-'),
                'appointments_count' => (int) ($patient->appointments_count ?? 0),
                'created_at' => $patient->created_at ? $patient->created_at->format('d/m/Y') : '-',
                'actions' => view('tenant.reports.patients.partials.actions', [
                    'patient' => $patient,
                ])->render(),
            ];
        })->all();

        return $this->gridResponse($paginator, $rows, ['summary' => $summary]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);
        $this->applySort($query, $request);

        return Excel::download(new ReportQueryExport(
            queryBuilder: $query,
            headingsRow: ['Nome', 'E-mail', 'Telefone', 'Agendamentos', 'Cadastrado em'],
            mapRow: static function (Patient $patient) {
                return [
                    $patient->full_name ?? 'N/A',
                    $patient->email ?? '-',
                    $patient->phone ?? '-',
                    (int) ($patient->appointments_count ?? 0),
                    $patient->created_at ? $patient->created_at->format('d/m/Y') : '-',
                ];
            },
        ), 'relatorio-pacientes-' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);
        $this->applySort($query, $request);

        $rows = $query->limit(self::PDF_MAX_ROWS + 1)->get();
        $truncated = $rows->count() > self::PDF_MAX_ROWS;

        if ($truncated) {
            $rows = $rows->take(self::PDF_MAX_ROWS);
        }

        return Pdf::loadView('tenant.reports.patients.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-pacientes-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $responsesFilter = function (Builder $appointmentsQuery) use ($request) {
            if ($request->filled('date_from')) {
                $appointmentsQuery->whereDate('starts_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $appointmentsQuery->whereDate('starts_at', '<=', $request->input('date_to'));
            }
        };

        $query = Patient::query()
            ->select('patients.*')
            ->withCount(['appointments as appointments_count' => $responsesFilter]);

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->whereHas('appointments', $responsesFilter);
        }

        return $query;
    }

    private function applySearch(Builder $query, Request $request): void
    {
        $term = $this->resolveSearchTerm($request);
        if ($term === '') {
            return;
        }

        $query->where(function (Builder $searchQuery) use ($term) {
            $like = '%' . $term . '%';

            $searchQuery
                ->where('patients.full_name', 'like', $like)
                ->orWhere('patients.email', 'like', $like)
                ->orWhere('patients.phone', 'like', $like)
                ->orWhere('patients.cpf', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'name' => 'patients.full_name',
            'email' => 'patients.email',
            'phone' => 'patients.phone',
            'appointments_count' => 'appointments_count',
            'created_at' => 'patients.created_at',
        ], 'patients.full_name', 'asc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('patients.full_name');
    }

    private function activeFilters(Request $request): array
    {
        return array_filter([
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $this->resolveSearchTerm($request),
            'sort' => $request->input('sort'),
            'dir' => $request->input('dir'),
        ], static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }
}
