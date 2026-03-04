<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\Doctor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DoctorReportController extends Controller
{
    use HasDoctorFilter;
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.doctors.index');
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total' => (clone $query)->count('doctors.id'),
            'active' => (clone $query)->where('users.status', 'active')->count('doctors.id'),
            'total_appointments' => $this->sumAppointmentsCount(clone $query),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (Doctor $doctor) {
            return [
                'name' => e($doctor->doctor_name ?? ($doctor->user?->name ?? 'N/A')),
                'specialties' => e($doctor->specialties->pluck('name')->filter()->join(', ') ?: 'N/A'),
                'appointments_count' => (int) ($doctor->appointments_count ?? 0),
                'status_badge' => view('tenant.reports.doctors.partials.status_badge', [
                    'status' => $doctor->doctor_status,
                ])->render(),
                'actions' => view('tenant.reports.doctors.partials.actions', [
                    'doctor' => $doctor,
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
            headingsRow: ['Nome', 'Especialidades', 'Status', 'Agendamentos'],
            mapRow: static function (Doctor $doctor) {
                return [
                    $doctor->doctor_name ?? ($doctor->user?->name ?? 'N/A'),
                    $doctor->specialties->pluck('name')->filter()->join(', ') ?: 'N/A',
                    ($doctor->doctor_status === 'active') ? 'Ativo' : 'Inativo',
                    (int) ($doctor->appointments_count ?? 0),
                ];
            },
        ), 'relatorio-medicos-' . now()->format('Ymd_His') . '.xlsx');
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

        return Pdf::loadView('tenant.reports.doctors.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-medicos-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $appointmentsFilter = function (Builder $appointmentsQuery) use ($request) {
            if ($request->filled('date_from')) {
                $appointmentsQuery->whereDate('starts_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $appointmentsQuery->whereDate('starts_at', '<=', $request->input('date_to'));
            }
        };

        $query = Doctor::query()
            ->select('doctors.*')
            ->selectRaw('users.name as doctor_name')
            ->selectRaw('users.status as doctor_status')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->with(['user', 'specialties'])
            ->withCount(['appointments as appointments_count' => $appointmentsFilter]);

        $this->applyDoctorFilter($query, 'doctors.id');

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
                ->where('users.name', 'like', $like)
                ->orWhereHas('specialties', function (Builder $specialtiesQuery) use ($like) {
                    $specialtiesQuery->where('medical_specialties.name', 'like', $like);
                });
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'name' => 'users.name',
            'appointments_count' => 'appointments_count',
            'status_badge' => 'users.status',
        ], 'users.name', 'asc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('users.name');
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

    private function sumAppointmentsCount(Builder $query): int
    {
        $connection = $query->getConnection();

        $baseQuery = $query
            ->toBase()
            ->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order']);

        return (int) $connection->query()
            ->fromSub($baseQuery, 'doctor_report')
            ->sum('appointments_count');
    }
}
