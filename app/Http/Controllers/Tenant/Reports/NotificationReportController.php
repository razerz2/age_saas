<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NotificationReportController extends Controller
{
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.notifications.index');
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total' => (clone $query)->count('notifications.id'),
            'read' => (clone $query)->whereNotNull('notifications.read_at')->count('notifications.id'),
            'unread' => (clone $query)->whereNull('notifications.read_at')->count('notifications.id'),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (Notification $notification) {
            return [
                'title' => e($notification->title ?? 'N/A'),
                'type' => e($notification->type ?? '-'),
                'status_badge' => view('tenant.reports.notifications.partials.status_badge', [
                    'isRead' => !is_null($notification->read_at),
                ])->render(),
                'created_at' => $notification->created_at ? $notification->created_at->format('d/m/Y H:i') : '-',
                'actions' => view('tenant.reports.notifications.partials.actions', [
                    'notification' => $notification,
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
            headingsRow: ['Titulo', 'Tipo', 'Status', 'Lida em', 'Criada em'],
            mapRow: static function (Notification $notification) {
                return [
                    $notification->title ?? 'N/A',
                    $notification->type ?? '-',
                    $notification->read_at ? 'Lida' : 'Nao lida',
                    $notification->read_at ? $notification->read_at->format('d/m/Y H:i') : '-',
                    $notification->created_at ? $notification->created_at->format('d/m/Y H:i') : '-',
                ];
            },
        ), 'relatorio-notificacoes-' . now()->format('Ymd_His') . '.xlsx');
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

        return Pdf::loadView('tenant.reports.notifications.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-notificacoes-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $query = Notification::query()->select('notifications.*');

        if ($request->filled('date_from')) {
            $query->whereDate('notifications.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('notifications.created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'read') {
                $query->whereNotNull('notifications.read_at');
            } elseif ($request->input('status') === 'unread') {
                $query->whereNull('notifications.read_at');
            }
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
                ->where('notifications.title', 'like', $like)
                ->orWhere('notifications.type', 'like', $like)
                ->orWhere('notifications.message', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'title' => 'notifications.title',
            'type' => 'notifications.type',
            'status_badge' => 'notifications.read_at',
            'created_at' => 'notifications.created_at',
        ], 'notifications.created_at', 'desc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('notifications.created_at', 'desc');
    }

    private function activeFilters(Request $request): array
    {
        return array_filter([
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status'),
            'search' => $this->resolveSearchTerm($request),
            'sort' => $request->input('sort'),
            'dir' => $request->input('dir'),
        ], static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }
}
