<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ✅ Pega o usuário autenticado do guard tenant
        $user = Auth::guard('tenant')->user();

        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // 🔹 1. Estatísticas principais
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalSpecialties = MedicalSpecialty::count();
        
        $appointmentsToday = Appointment::whereBetween('starts_at', [$todayStart, $todayEnd])->count();
        $appointmentsWeek = Appointment::whereBetween('starts_at', [$weekStart, $weekEnd])->count();
        $appointmentsMonth = Appointment::whereBetween('starts_at', [$monthStart, $monthEnd])->count();

        // Calcular variações percentuais (comparando com período anterior)
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();
        $lastMonthStart = $monthStart->copy()->subMonth();
        $lastMonthEnd = $monthEnd->copy()->subMonth();

        $lastWeekAppointments = Appointment::whereBetween('starts_at', [$lastWeekStart, $lastWeekEnd])->count();
        $lastMonthAppointments = Appointment::whereBetween('starts_at', [$lastMonthStart, $lastMonthEnd])->count();

        $weekVariation = $lastWeekAppointments > 0 
            ? round((($appointmentsWeek - $lastWeekAppointments) / $lastWeekAppointments) * 100, 1)
            : ($appointmentsWeek > 0 ? 100 : 0);

        $monthVariation = $lastMonthAppointments > 0
            ? round((($appointmentsMonth - $lastMonthAppointments) / $lastMonthAppointments) * 100, 1)
            : ($appointmentsMonth > 0 ? 100 : 0);

        $stats = [
            'patients' => [
                'total' => $totalPatients,
                'variation' => 0, // Pode ser calculado se necessário
            ],
            'doctors' => [
                'total' => $totalDoctors,
                'variation' => 0,
            ],
            'specialties' => [
                'total' => $totalSpecialties,
                'variation' => 0,
            ],
            'today' => [
                'total' => $appointmentsToday,
                'variation' => 0,
            ],
            'week' => [
                'total' => $appointmentsWeek,
                'variation' => $weekVariation,
            ],
            'month' => [
                'total' => $appointmentsMonth,
                'variation' => $monthVariation,
            ],
        ];

        // 🔹 2. Próximos agendamentos (próximas 24 horas)
        $next24Hours = $now->copy()->addDay();
        $appointmentsNext = Appointment::with(['patient', 'calendar.doctor.user', 'specialty', 'type'])
            ->whereBetween('starts_at', [$now, $next24Hours])
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->orderBy('starts_at', 'asc')
            ->limit(5)
            ->get();

        // 🔹 3. Gráfico de linha - Agendamentos últimos 12 meses
        $chartLast12Months = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $count = Appointment::whereBetween('starts_at', [$monthStart, $monthEnd])->count();
            
            $chartLast12Months[] = [
                'month' => $month->translatedFormat('M/Y'),
                'short' => $month->translatedFormat('M'),
                'total' => $count,
            ];
        }

        // 🔹 4. Gráfico de pizza - Distribuição por Especialidade
        $chartBySpecialty = Appointment::select('specialty_id', DB::raw('count(*) as total'))
            ->whereNotNull('specialty_id')
            ->groupBy('specialty_id')
            ->with('specialty')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->specialty->name ?? 'Sem especialidade',
                    'value' => $item->total,
                ];
            })
            ->sortByDesc('value')
            ->take(6)
            ->values();

        // 🔹 5. Consultórios ativos hoje (médicos com agendamentos hoje)
        $activeDoctorsToday = Doctor::with(['user', 'specialties', 'calendars'])
            ->whereHas('calendars.appointments', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('starts_at', [$todayStart, $todayEnd])
                    ->whereIn('status', ['scheduled', 'rescheduled']);
            })
            ->get()
            ->map(function ($doctor) use ($todayStart, $todayEnd) {
                $appointments = Appointment::whereHas('calendar', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                })
                ->whereBetween('starts_at', [$todayStart, $todayEnd])
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->orderBy('starts_at', 'asc')
                ->get();

                $specialties = $doctor->specialties->pluck('name')->join(', ') ?: 'Sem especialidade';
                $times = $appointments->map(function ($apt) {
                    return $apt->starts_at->format('H:i');
                })->take(5)->join(', ');

                return [
                    'doctor' => $doctor->user->name ?? 'Sem nome',
                    'specialty' => $specialties,
                    'count' => $appointments->count(),
                    'times' => $times ?: 'Nenhum',
                ];
            })
            ->take(5);

        return view('tenant.dashboard.index', compact(
            'user',
            'stats',
            'appointmentsNext',
            'chartLast12Months',
            'chartBySpecialty',
            'activeDoctorsToday'
        ));
    }
}
