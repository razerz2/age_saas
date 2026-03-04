<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\RecurringAppointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecurringReportController extends Controller
{
    use HasDoctorFilter;
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        return view('tenant.reports.recurring.index');
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = [
            'total' => (clone $query)->count('recurring_appointments.id'),
            'active' => (clone $query)->where('recurring_appointments.active', true)->count('recurring_appointments.id'),
        ];

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (RecurringAppointment $recurring) {
            return [
                'doctor' => e($recurring->doctor_name ?? ($recurring->doctor?->user?->name ?? 'N/A')),
                'patient' => e($recurring->patient_name ?? ($recurring->patient?->full_name ?? 'N/A')),
                'appointment_type' => e($recurring->appointment_type_name ?? ($recurring->appointmentType?->name ?? 'N/A')),
                'start_date' => $recurring->start_date ? $recurring->start_date->format('d/m/Y') : '-',
                'status_badge' => view('tenant.reports.recurring.partials.status_badge', [
                    'isActive' => (bool) ($recurring->active ?? false),
                ])->render(),
                'created_at' => $recurring->created_at ? $recurring->created_at->format('d/m/Y H:i') : '-',
                'actions' => view('tenant.reports.recurring.partials.actions', [
                    'recurring' => $recurring,
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
            headingsRow: ['Medico', 'Paciente', 'Tipo de consulta', 'Data inicial', 'Status', 'Criado em'],
            mapRow: static function (RecurringAppointment $recurring) {
                return [
                    $recurring->doctor_name ?? ($recurring->doctor?->user?->name ?? 'N/A'),
                    $recurring->patient_name ?? ($recurring->patient?->full_name ?? 'N/A'),
                    $recurring->appointment_type_name ?? ($recurring->appointmentType?->name ?? 'N/A'),
                    $recurring->start_date ? $recurring->start_date->format('d/m/Y') : '-',
                    ($recurring->active ?? false) ? 'Ativo' : 'Inativo',
                    $recurring->created_at ? $recurring->created_at->format('d/m/Y H:i') : '-',
                ];
            },
        ), 'relatorio-recorrencias-' . now()->format('Ymd_His') . '.xlsx');
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

        return Pdf::loadView('tenant.reports.recurring.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape')->download('relatorio-recorrencias-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $query = RecurringAppointment::query()
            ->select('recurring_appointments.*')
            ->selectRaw('doctor_users.name as doctor_name')
            ->selectRaw('patients.full_name as patient_name')
            ->selectRaw('appointment_types.name as appointment_type_name')
            ->leftJoin('doctors', 'doctors.id', '=', 'recurring_appointments.doctor_id')
            ->leftJoin('users as doctor_users', 'doctor_users.id', '=', 'doctors.user_id')
            ->leftJoin('patients', 'patients.id', '=', 'recurring_appointments.patient_id')
            ->leftJoin('appointment_types', 'appointment_types.id', '=', 'recurring_appointments.appointment_type_id')
            ->with(['doctor.user', 'patient', 'appointmentType']);

        $this->applyDoctorFilter($query, 'recurring_appointments.doctor_id');

        if ($request->filled('doctor_id')) {
            $query->where('recurring_appointments.doctor_id', $request->input('doctor_id'));
        }

        if ($request->filled('active')) {
            $query->where('recurring_appointments.active', filter_var($request->input('active'), FILTER_VALIDATE_BOOL));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('recurring_appointments.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('recurring_appointments.created_at', '<=', $request->input('date_to'));
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
                ->where('doctor_users.name', 'like', $like)
                ->orWhere('patients.full_name', 'like', $like)
                ->orWhere('appointment_types.name', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'doctor' => 'doctor_users.name',
            'patient' => 'patients.full_name',
            'appointment_type' => 'appointment_types.name',
            'start_date' => 'recurring_appointments.start_date',
            'status_badge' => 'recurring_appointments.active',
            'created_at' => 'recurring_appointments.created_at',
        ], 'recurring_appointments.created_at', 'desc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('recurring_appointments.created_at', 'desc');
    }

    private function activeFilters(Request $request): array
    {
        return array_filter([
            'doctor_id' => $request->input('doctor_id'),
            'active' => $request->input('active'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $this->resolveSearchTerm($request),
            'sort' => $request->input('sort'),
            'dir' => $request->input('dir'),
        ], static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }
}
