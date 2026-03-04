<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\Form;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FormReportController extends Controller
{
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.forms.index');
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total' => (clone $query)->count('forms.id'),
            'total_responses' => $this->sumResponsesCount(clone $query),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (Form $form) {
            return [
                'name' => e($form->name ?? 'N/A'),
                'responses_count' => (int) ($form->responses_count ?? 0),
                'status_badge' => view('tenant.reports.forms.partials.status_badge', [
                    'isActive' => (bool) ($form->is_active ?? false),
                ])->render(),
                'created_at' => $form->created_at ? $form->created_at->format('d/m/Y') : '-',
                'actions' => view('tenant.reports.forms.partials.actions', [
                    'form' => $form,
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
            headingsRow: ['Nome', 'Respostas', 'Status', 'Criado em'],
            mapRow: static function (Form $form) {
                return [
                    $form->name ?? 'N/A',
                    (int) ($form->responses_count ?? 0),
                    ($form->is_active ?? false) ? 'Ativo' : 'Inativo',
                    $form->created_at ? $form->created_at->format('d/m/Y') : '-',
                ];
            },
        ), 'relatorio-formularios-' . now()->format('Ymd_His') . '.xlsx');
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

        return Pdf::loadView('tenant.reports.forms.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-formularios-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $responsesFilter = function (Builder $responsesQuery) use ($request) {
            if ($request->filled('date_from')) {
                $responsesQuery->whereDate('submitted_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $responsesQuery->whereDate('submitted_at', '<=', $request->input('date_to'));
            }
        };

        $query = Form::query()
            ->select('forms.*')
            ->withCount(['responses as responses_count' => $responsesFilter]);

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->whereHas('responses', $responsesFilter);
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
                ->where('forms.name', 'like', $like)
                ->orWhere('forms.description', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'name' => 'forms.name',
            'responses_count' => 'responses_count',
            'status_badge' => 'forms.is_active',
            'created_at' => 'forms.created_at',
        ], 'forms.name', 'asc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('forms.name');
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

    private function sumResponsesCount(Builder $query): int
    {
        $connection = $query->getConnection();

        $baseQuery = $query
            ->toBase()
            ->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order']);

        return (int) $connection->query()
            ->fromSub($baseQuery, 'form_report')
            ->sum('responses_count');
    }
}
