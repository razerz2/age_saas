<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\AppointmentType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentReportController extends Controller
{
    use HasDoctorFilter;

    public function index()
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        return view('tenant.reports.appointments.index', compact('doctors', 'specialties', 'appointmentTypes'));
    }

    public function data(Request $request)
    {
        $query = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty']);

        // Aplicar filtro de médico
        $this->applyDoctorFilterWhereHas($query, 'calendar', 'doctor_id');

        // Filtros dinâmicos
        if ($request->filled('doctor_id')) {
            $query->whereHas('calendar', function($q) use ($request) {
                $q->where('doctor_id', $request->doctor_id);
            });
        }

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        if ($request->filled('appointment_type')) {
            $query->where('appointment_type', $request->appointment_type);
        }

        if ($request->filled('appointment_mode')) {
            $query->where('appointment_mode', $request->appointment_mode);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origin')) {
            // Lógica para origem (admin / público / portal) - pode ser baseada em notes ou outro campo
            // Por enquanto, vamos usar um campo hipotético ou notes
        }

        // Período
        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        // Períodos rápidos
        if ($request->filled('period')) {
            $now = Carbon::now();
            switch ($request->period) {
                case 'today':
                    $query->whereDate('starts_at', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('starts_at', [$now->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('starts_at', $now->month)->whereYear('starts_at', $now->year);
                    break;
                case 'last_30_days':
                    $query->where('starts_at', '>=', $now->subDays(30));
                    break;
            }
        }

        $appointments = $query->orderBy('starts_at', 'desc')->get();

        // Resumo
        $summary = [
            'total' => $appointments->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'attended' => $appointments->where('status', 'attended')->count(),
            'canceled' => $appointments->where('status', 'canceled')->count(),
            'online' => $appointments->where('appointment_mode', 'online')->count(),
            'presencial' => $appointments->where('appointment_mode', 'presencial')->count(),
        ];

        // Gráfico de evolução (linha)
        $evolution = $appointments->groupBy(function($item) {
            return $item->starts_at->format('Y-m-d');
        })->map->count();

        // Gráfico de pizza (online x presencial)
        $modeChart = [
            'online' => $appointments->where('appointment_mode', 'online')->count(),
            'presencial' => $appointments->where('appointment_mode', 'presencial')->count(),
        ];

        // Gráfico de barras (por médico)
        $byDoctor = $appointments->groupBy(function($item) {
            return $item->calendar->doctor->user->name ?? 'N/A';
        })->map->count();

        // Heatmap (horários por dia da semana)
        $heatmap = [];
        $daysOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $hours = range(8, 18); // 8h às 18h

        foreach ($daysOfWeek as $dayIndex => $dayName) {
            foreach ($hours as $hour) {
                $count = $appointments->filter(function($apt) use ($dayIndex, $hour) {
                    return $apt->starts_at->dayOfWeek == $dayIndex && $apt->starts_at->hour == $hour;
                })->count();
                $heatmap[$dayName][$hour] = $count;
            }
        }

        // Tabela
        $table = $appointments->map(function($apt) {
            return [
                'id' => $apt->id,
                'patient' => $apt->patient->full_name ?? 'N/A',
                'doctor' => $apt->calendar->doctor->user->name ?? 'N/A',
                'specialty' => $apt->specialty->name ?? 'N/A',
                'type' => $apt->type->name ?? 'N/A',
                'date' => $apt->starts_at->format('d/m/Y'),
                'time' => $apt->starts_at->format('H:i'),
                'mode' => $apt->appointment_mode ?? 'presencial',
                'status' => $apt->status,
                'status_translated' => $apt->status_translated,
            ];
        });

        return response()->json([
            'summary' => $summary,
            'chart' => [
                'evolution' => $evolution,
                'mode' => $modeChart,
                'byDoctor' => $byDoctor,
                'heatmap' => $heatmap,
            ],
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request)
    {
        // Implementar exportação Excel usando Maatwebsite\Excel
        // Por enquanto, retornar JSON
        return response()->json(['message' => 'Exportação Excel em desenvolvimento']);
    }

    public function exportPdf(Request $request)
    {
        // Implementar exportação PDF usando dompdf
        return response()->json(['message' => 'Exportação PDF em desenvolvimento']);
    }

    public function exportCsv(Request $request)
    {
        // Implementar exportação CSV
        return response()->json(['message' => 'Exportação CSV em desenvolvimento']);
    }
}

