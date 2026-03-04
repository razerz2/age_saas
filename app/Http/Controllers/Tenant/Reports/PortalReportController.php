<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportArrayExport;
use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\PatientLogin;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class PortalReportController extends Controller
{
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.portal.index', [
            'loginTableExists' => $this->loginTableExists(),
        ]);
    }

    public function gridData(Request $request)
    {
        if (!$this->loginTableExists()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'page' => 1,
                    'per_page' => $this->resolvePerPage($request),
                    'total' => 0,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                ],
                'summary' => [
                    'total_logins' => 0,
                    'active' => 0,
                ],
            ]);
        }

        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total_logins' => (clone $query)->count('patient_logins.id'),
            'active' => (clone $query)->where('patient_logins.is_active', true)->count('patient_logins.id'),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (PatientLogin $login) {
            return [
                'patient' => e($login->patient_name ?? ($login->patient?->full_name ?? 'N/A')),
                'email' => e($login->email ?? '-'),
                'status_badge' => view('tenant.reports.portal.partials.status_badge', [
                    'isActive' => (bool) ($login->is_active ?? false),
                ])->render(),
                'created_at' => $login->created_at ? $login->created_at->format('d/m/Y H:i') : '-',
                'actions' => view('tenant.reports.portal.partials.actions', [
                    'login' => $login,
                ])->render(),
            ];
        })->all();

        return $this->gridResponse($paginator, $rows, ['summary' => $summary]);
    }

    public function exportExcel(Request $request)
    {
        if (!$this->loginTableExists()) {
            return Excel::download(new ReportArrayExport(
                rows: [],
                headingsRow: ['Paciente', 'E-mail', 'Status', 'Criado em'],
            ), 'relatorio-portal-paciente-' . now()->format('Ymd_His') . '.xlsx');
        }

        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);
        $this->applySort($query, $request);

        return Excel::download(new ReportQueryExport(
            queryBuilder: $query,
            headingsRow: ['Paciente', 'E-mail', 'Status', 'Criado em'],
            mapRow: static function (PatientLogin $login) {
                return [
                    $login->patient_name ?? ($login->patient?->full_name ?? 'N/A'),
                    $login->email ?? '-',
                    ($login->is_active ?? false) ? 'Ativo' : 'Inativo',
                    $login->created_at ? $login->created_at->format('d/m/Y H:i') : '-',
                ];
            },
        ), 'relatorio-portal-paciente-' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        if (!$this->loginTableExists()) {
            return Pdf::loadView('tenant.reports.portal.pdf', [
                'rows' => collect(),
                'generatedAt' => now(),
                'activeFilters' => $this->activeFilters($request),
                'truncated' => false,
                'pdfMaxRows' => self::PDF_MAX_ROWS,
            ])->setPaper('a4', 'landscape')->download('relatorio-portal-paciente-' . now()->format('Ymd_His') . '.pdf');
        }

        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);
        $this->applySort($query, $request);

        $rows = $query->limit(self::PDF_MAX_ROWS + 1)->get();
        $truncated = $rows->count() > self::PDF_MAX_ROWS;

        if ($truncated) {
            $rows = $rows->take(self::PDF_MAX_ROWS);
        }

        return Pdf::loadView('tenant.reports.portal.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-portal-paciente-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $query = PatientLogin::query()
            ->select('patient_logins.*')
            ->selectRaw('patients.full_name as patient_name')
            ->leftJoin('patients', 'patients.id', '=', 'patient_logins.patient_id')
            ->with('patient');

        if ($request->filled('date_from')) {
            $query->whereDate('patient_logins.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('patient_logins.created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('is_active')) {
            $query->where('patient_logins.is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
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
                ->orWhere('patient_logins.email', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'patient' => 'patients.full_name',
            'email' => 'patient_logins.email',
            'status_badge' => 'patient_logins.is_active',
            'created_at' => 'patient_logins.created_at',
        ], 'patient_logins.created_at', 'desc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('patient_logins.created_at', 'desc');
    }

    private function activeFilters(Request $request): array
    {
        return array_filter([
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'is_active' => $request->input('is_active'),
            'search' => $this->resolveSearchTerm($request),
            'sort' => $request->input('sort'),
            'dir' => $request->input('dir'),
        ], static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function loginTableExists(): bool
    {
        try {
            return Schema::connection('tenant')->hasTable('patient_logins');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
