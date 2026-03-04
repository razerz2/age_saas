<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Exports\Tenant\Reports\ReportQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Http\Controllers\Tenant\Reports\Concerns\HandlesReportRequests;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentReportController extends Controller
{
    use HasDoctorFilter;
    use HandlesReportRequests;

    private const PDF_MAX_ROWS = 5000;

    public function index()
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        return view('tenant.reports.appointments.index', compact('doctors', 'specialties', 'appointmentTypes'));
    }

    public function gridData(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);

        $summary = $this->buildSummary(clone $query);
        $chart = $this->buildCharts(clone $query);

        $this->applySort($query, $request);

        $paginator = $this->paginateQuery($query, $request);

        $rows = $paginator->getCollection()->map(function (Appointment $appointment) {
            return [
                'patient' => e($appointment->patient_name ?? 'N/A'),
                'doctor' => e($appointment->doctor_name ?? 'N/A'),
                'specialty' => e($appointment->specialty_name ?? 'N/A'),
                'type' => e($appointment->appointment_type_name ?? 'N/A'),
                'date' => $appointment->starts_at ? $appointment->starts_at->format('d/m/Y') : '-',
                'time' => $appointment->starts_at ? $appointment->starts_at->format('H:i') : '-',
                'mode_badge' => view('tenant.reports.appointments.partials.mode_badge', [
                    'mode' => $appointment->appointment_mode,
                ])->render(),
                'status_badge' => view('tenant.reports.appointments.partials.status_badge', [
                    'status' => $appointment->status,
                    'statusLabel' => $appointment->status_translated,
                ])->render(),
                'actions' => view('tenant.reports.appointments.partials.actions', [
                    'appointment' => $appointment,
                ])->render(),
            ];
        })->all();

        return $this->gridResponse($paginator, $rows, [
            'summary' => $summary,
            'chart' => $chart,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->buildBaseQuery($request);
        $this->applySearch($query, $request);
        $this->applySort($query, $request);

        $filename = 'relatorio-agendamentos-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ReportQueryExport(
            queryBuilder: $query,
            headingsRow: [
                'Paciente',
                'Medico',
                'Especialidade',
                'Tipo',
                'Data',
                'Hora',
                'Modo',
                'Status',
                'Origem',
            ],
            mapRow: function (Appointment $appointment) {
                return [
                    $appointment->patient_name ?? 'N/A',
                    $appointment->doctor_name ?? 'N/A',
                    $appointment->specialty_name ?? 'N/A',
                    $appointment->appointment_type_name ?? 'N/A',
                    $appointment->starts_at ? $appointment->starts_at->format('d/m/Y') : '-',
                    $appointment->starts_at ? $appointment->starts_at->format('H:i') : '-',
                    $appointment->appointment_mode === 'online' ? 'Online' : 'Presencial',
                    $appointment->status_translated,
                    $appointment->origin ?? '-',
                ];
            },
        ), $filename);
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

        $pdf = Pdf::loadView('tenant.reports.appointments.pdf', [
            'rows' => $rows,
            'generatedAt' => now(),
            'activeFilters' => $this->activeFilters($request),
            'truncated' => $truncated,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('relatorio-agendamentos-' . now()->format('Ymd_His') . '.pdf');
    }

    private function buildBaseQuery(Request $request): Builder
    {
        $query = Appointment::query()
            ->select('appointments.*')
            ->selectRaw('patients.full_name as patient_name')
            ->selectRaw('doctor_users.name as doctor_name')
            ->selectRaw('medical_specialties.name as specialty_name')
            ->selectRaw('appointment_types.name as appointment_type_name')
            ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
            ->leftJoin('calendars', 'calendars.id', '=', 'appointments.calendar_id')
            ->leftJoin('doctors', 'doctors.id', '=', 'calendars.doctor_id')
            ->leftJoin('users as doctor_users', 'doctor_users.id', '=', 'doctors.user_id')
            ->leftJoin('medical_specialties', 'medical_specialties.id', '=', 'appointments.specialty_id')
            ->leftJoin('appointment_types', 'appointment_types.id', '=', 'appointments.appointment_type');

        $this->applyDoctorFilter($query, 'appointments.doctor_id');

        if ($request->filled('doctor_id')) {
            $query->where('appointments.doctor_id', $request->input('doctor_id'));
        }

        if ($request->filled('specialty_id')) {
            $query->where('appointments.specialty_id', $request->input('specialty_id'));
        }

        if ($request->filled('appointment_type')) {
            $query->where('appointments.appointment_type', $request->input('appointment_type'));
        }

        if ($request->filled('appointment_mode')) {
            $query->where('appointments.appointment_mode', $request->input('appointment_mode'));
        }

        if ($request->filled('status')) {
            $query->where('appointments.status', $request->input('status'));
        }

        if ($request->filled('origin')) {
            $query->where('appointments.origin', $request->input('origin'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointments.starts_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('appointments.starts_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('period')) {
            $now = Carbon::now();

            switch ($request->input('period')) {
                case 'today':
                    $query->whereDate('appointments.starts_at', $now->toDateString());
                    break;

                case 'week':
                    $query->whereBetween('appointments.starts_at', [
                        $now->copy()->startOfWeek(),
                        $now->copy()->endOfWeek(),
                    ]);
                    break;

                case 'month':
                    $query->whereYear('appointments.starts_at', $now->year)
                        ->whereMonth('appointments.starts_at', $now->month);
                    break;

                case 'last_30_days':
                    $query->where('appointments.starts_at', '>=', $now->copy()->subDays(30));
                    break;
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
                ->where('patients.full_name', 'like', $like)
                ->orWhere('doctor_users.name', 'like', $like)
                ->orWhere('medical_specialties.name', 'like', $like)
                ->orWhere('appointment_types.name', 'like', $like)
                ->orWhere('appointments.status', 'like', $like)
                ->orWhere('appointments.appointment_mode', 'like', $like)
                ->orWhere('appointments.origin', 'like', $like);
        });
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request, [
            'patient' => 'patients.full_name',
            'doctor' => 'doctor_users.name',
            'specialty' => 'medical_specialties.name',
            'type' => 'appointment_types.name',
            'date' => 'appointments.starts_at',
            'time' => 'appointments.starts_at',
            'mode_badge' => 'appointments.appointment_mode',
            'status_badge' => 'appointments.status',
        ], 'appointments.starts_at', 'desc');

        $query
            ->orderBy($sort['column'], $sort['direction'])
            ->orderBy('appointments.starts_at', 'desc');
    }

    private function buildSummary(Builder $query): array
    {
        return [
            'total' => (clone $query)->count('appointments.id'),
            'scheduled' => (clone $query)->where('appointments.status', 'scheduled')->count('appointments.id'),
            'attended' => (clone $query)->where('appointments.status', 'attended')->count('appointments.id'),
            'canceled' => (clone $query)->where('appointments.status', 'canceled')->count('appointments.id'),
            'online' => (clone $query)->where('appointments.appointment_mode', 'online')->count('appointments.id'),
            'presencial' => (clone $query)->where('appointments.appointment_mode', 'presencial')->count('appointments.id'),
        ];
    }

    private function buildCharts(Builder $query): array
    {
        $evolutionRows = $this->newChartAggregateQuery($query)
            ->selectRaw('DATE(appointments.starts_at) as report_date')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw('DATE(appointments.starts_at)')
            ->orderBy('report_date')
            ->get();

        $evolution = [];
        foreach ($evolutionRows as $row) {
            $evolution[$row->report_date] = (int) $row->total;
        }

        $modeRows = $this->newChartAggregateQuery($query)
            ->selectRaw('appointments.appointment_mode as mode')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('appointments.appointment_mode')
            ->get();

        $mode = [
            'online' => 0,
            'presencial' => 0,
        ];

        foreach ($modeRows as $row) {
            if (isset($mode[$row->mode])) {
                $mode[$row->mode] = (int) $row->total;
            }
        }

        $byDoctorRows = $this->newChartAggregateQuery($query)
            ->selectRaw("COALESCE(doctor_users.name, 'N/A') as doctor_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('doctor_users.name')
            ->orderBy('doctor_users.name')
            ->get();

        $byDoctor = [];
        foreach ($byDoctorRows as $row) {
            $byDoctor[$row->doctor_name ?? 'N/A'] = (int) $row->total;
        }

        $days = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
        $hours = range(8, 18);

        $heatmap = [];
        foreach ($days as $dayName) {
            foreach ($hours as $hour) {
                $heatmap[$dayName][$hour] = 0;
            }
        }

        $isPgsql = $query->getConnection()->getDriverName() === 'pgsql';
        $dayExpression = $isPgsql
            ? 'EXTRACT(DOW FROM appointments.starts_at)'
            : 'DAYOFWEEK(appointments.starts_at)';
        $hourExpression = $isPgsql
            ? 'EXTRACT(HOUR FROM appointments.starts_at)'
            : 'HOUR(appointments.starts_at)';

        $heatmapRows = $this->newChartAggregateQuery($query)
            ->selectRaw($dayExpression . ' as day_number')
            ->selectRaw($hourExpression . ' as hour_slot')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw($dayExpression)
            ->groupByRaw($hourExpression)
            ->get();

        foreach ($heatmapRows as $row) {
            $dayIndex = $isPgsql
                ? (int) $row->day_number
                : ((int) $row->day_number + 6) % 7;
            $hour = (int) $row->hour_slot;

            if (!isset($days[$dayIndex]) || !in_array($hour, $hours, true)) {
                continue;
            }

            $heatmap[$days[$dayIndex]][$hour] = (int) $row->total;
        }

        return [
            'evolution' => $evolution,
            'mode' => $mode,
            'byDoctor' => $byDoctor,
            'heatmap' => $heatmap,
        ];
    }

    private function newChartAggregateQuery(Builder $query): QueryBuilder
    {
        return (clone $query)
            ->toBase()
            ->cloneWithout(['columns', 'orders'])
            ->cloneWithoutBindings(['select', 'order']);
    }

    private function activeFilters(Request $request): array
    {
        return array_filter([
            'period' => $request->input('period'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'doctor_id' => $request->input('doctor_id'),
            'specialty_id' => $request->input('specialty_id'),
            'appointment_type' => $request->input('appointment_type'),
            'appointment_mode' => $request->input('appointment_mode'),
            'status' => $request->input('status'),
            'origin' => $request->input('origin'),
            'search' => $this->resolveSearchTerm($request),
            'sort' => $request->input('sort'),
            'dir' => $request->input('dir'),
        ], static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }
}
