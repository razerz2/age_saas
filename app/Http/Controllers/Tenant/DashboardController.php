<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ? Pega o usuário autenticado do guard tenant
        $user = Auth::guard('tenant')->user();
        $currentTenant = tenant();

        $activeTrialSubscription = null;
        $expiredTrialSubscription = null;
        $trialDaysRemaining = null;
        $trialExpiringSoon = false;
        $trialExpired = false;

        if ($currentTenant instanceof PlatformTenant) {
            $activeTrialSubscription = $currentTenant->activeTrialSubscription();
            $expiredTrialSubscription = $currentTenant->expiredTrialSubscription();
            $trialDaysRemaining = $currentTenant->trialDaysRemaining();
            $trialExpiringSoon = $trialDaysRemaining !== null && $trialDaysRemaining <= 3;
            $trialExpired = ! $currentTenant->isEligibleForAccess() && $expiredTrialSubscription !== null;
        }

        if ($trialExpired) {
            return view('tenant.dashboard.trial-expired', [
                'user' => $user,
                'currentTenant' => $currentTenant,
                'expiredTrialSubscription' => $expiredTrialSubscription,
                'blockedMessage' => method_exists($currentTenant, 'commercialAccessBlockedMessage')
                    ? $currentTenant->commercialAccessBlockedMessage()
                    : 'Seu acesso foi pausado. Escolha um plano para continuar usando o sistema.',
            ]);
        }

        // ?? Resolver período filtrado
        $period = $this->resolvePeriod();
        $periodDescription = $this->getPeriodDescription($period);

        // ?? Gerar chave de cache única para tenant + período
        $cacheKey = $this->generateCacheKey($period);

        // ?? Tentar obter dados do cache (15 minutos)
        $dashboardData = Cache::remember($cacheKey, 900, function () use ($period) {
            return [
                'stats' => $this->getPeriodStats($period),
                'appointmentsNext' => $this->getNextAppointments(),
                'chartLast12Months' => $this->get12MonthsChart(),
                'chartBySpecialty' => $this->getSpecialtyChart($period),
                'summary' => $this->getPeriodSummary($period),
                'activeDoctorsToday' => $this->getActiveDoctors($period),
            ];
        });

        return view('tenant.dashboard.index', array_merge($dashboardData, [
            'user' => $user,
            'periodDescription' => $periodDescription,
            'activeTrialSubscription' => $activeTrialSubscription,
            'expiredTrialSubscription' => $expiredTrialSubscription,
            'trialDaysRemaining' => $trialDaysRemaining,
            'trialExpiringSoon' => $trialExpiringSoon,
            'trialExpired' => $trialExpired,
        ]));
    }

    /**
     * Gera chave de cache única baseada no tenant e período
     */
    private function generateCacheKey($period)
    {
        $tenantId = tenant('id');
        $periodKey = $period['type'] . '_' . $period['start']->format('Y-m-d') . '_' . $period['end']->format('Y-m-d');
        return "dashboard_{$tenantId}_{$periodKey}";
    }

    /**
     * Resolve o período filtrado baseado nos parâmetros da requisição
     */
    private function resolvePeriod()
    {
        $now = Carbon::now();
        
        if (request('start_date') && request('end_date')) {
            // Período customizado
            return [
                'start' => Carbon::parse(request('start_date'))->startOfDay(),
                'end' => Carbon::parse(request('end_date'))->endOfDay(),
                'type' => 'custom'
            ];
        }

        switch (request('period')) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'type' => 'today'
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'type' => 'week'
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'type' => 'month'
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'type' => 'year'
                ];
            default:
                // Padrão: mês atual
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'type' => 'month'
                ];
        }
    }

    /**
     * Retorna descrição do período para exibição
     */
    private function getPeriodDescription($period)
    {
        switch ($period['type']) {
            case 'today':
                return 'Hoje - ' . $period['start']->format('d/m/Y');
            case 'week':
                return $period['start']->format('d/m') . ' a ' . $period['end']->format('d/m/Y');
            case 'month':
                return ucfirst($period['start']->translatedFormat('F/Y'));
            case 'year':
                return $period['start']->format('Y');
            case 'custom':
                return $period['start']->format('d/m/Y') . ' a ' . $period['end']->format('d/m/Y');
            default:
                return null;
        }
    }

    /**
     * Calcula estatísticas do período
     */
    private function getPeriodStats($period)
    {
        // Totais gerais (não filtrados por período) - com cache mais longo
        $generalStats = Cache::remember('general_stats_' . tenant('id'), 3600, function () {
            return [
                'totalPatients' => Patient::count(),
                'totalDoctors' => Doctor::count(),
                'totalSpecialties' => MedicalSpecialty::count(),
            ];
        });
        
        // Agendamentos do período
        $periodAppointments = Appointment::whereBetween('starts_at', [$period['start'], $period['end']])->count();
        
        // Calcular variação comparando com período anterior
        $previousPeriod = $this->getPreviousPeriod($period);
        $previousAppointments = Appointment::whereBetween('starts_at', [$previousPeriod['start'], $previousPeriod['end']])->count();

        $periodVariation = $previousAppointments > 0 
            ? round((($periodAppointments - $previousAppointments) / $previousAppointments) * 100, 1)
            : ($periodAppointments > 0 ? 100 : 0);

        return [
            'period' => [
                'total' => $periodAppointments,
                'variation' => $periodVariation,
            ],
            'patients' => [
                'total' => $generalStats['totalPatients'],
                'variation' => 0, // Pode ser calculado se necessário
            ],
            'doctors' => [
                'total' => $generalStats['totalDoctors'],
                'variation' => 0,
            ],
            'specialties' => [
                'total' => $generalStats['totalSpecialties'],
                'variation' => 0,
            ],
        ];
    }

    /**
     * Retorna o período anterior para cálculo de variação
     */
    private function getPreviousPeriod($period)
    {
        $duration = $period['end']->diffInDays($period['start']) + 1;
        
        return [
            'start' => $period['start']->copy()->subDays($duration),
            'end' => $period['end']->copy()->subDays($duration),
            'type' => $period['type']
        ];
    }

    /**
     * Próximos agendamentos (próximas 24 horas) - sem cache para dados em tempo real
     */
    private function getNextAppointments()
    {
        $now = Carbon::now();
        $next24Hours = $now->copy()->addDay();
        
        return Appointment::with(['patient', 'calendar.doctor.user', 'specialty', 'type'])
            ->whereBetween('starts_at', [$now, $next24Hours])
            ->whereIn('status', ['scheduled', 'rescheduled', 'confirmed'])
            ->orderBy('starts_at', 'asc')
            ->limit(5)
            ->get();
    }

    /**
     * Gráfico de agendamentos últimos 12 meses - cache longo
     */
    private function get12MonthsChart()
    {
        $cacheKey = 'chart_12months_' . tenant('id');
        
        return Cache::remember($cacheKey, 3600, function () {
            $now = Carbon::now();
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
            
            return $chartLast12Months;
        });
    }

    /**
     * Distribuição por especialidade no período
     */
    private function getSpecialtyChart($period)
    {
        return Appointment::select('specialty_id', DB::raw('count(*) as total'))
            ->whereBetween('starts_at', [$period['start'], $period['end']])
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
    }

    /**
     * Resumo do período com métricas de performance
     */
    private function getPeriodSummary($period)
    {
        $appointments = Appointment::whereBetween('starts_at', [$period['start'], $period['end']])
            ->get();

        $total = $appointments->count();
        $attended = $appointments->whereIn('status', ['attended', 'completed'])->count();
        $cancelled = $appointments->whereIn('status', ['cancelled', 'canceled'])->count();
        
        // Taxa de comparecimento
        $attendanceRate = $total > 0 ? ($attended / $total) * 100 : 0;
        
        // Tempo médio de espera (simulado - baseado em dados reais se disponível)
        $avgWaitTime = 15; // minutos (pode ser calculado real se houver dados de check-in)

        return [
            'attendance_rate' => round($attendanceRate, 1),
            'cancellations' => $cancelled,
            'avg_wait_time' => $avgWaitTime,
        ];
    }

    /**
     * Profissionais em destaque do período
     */
    private function getActiveDoctors($period)
    {
        return Doctor::with(['user', 'specialties', 'calendars'])
            ->whereHas('calendars.appointments', function ($query) use ($period) {
                $query->whereBetween('starts_at', [$period['start'], $period['end']])
                    ->whereIn('status', ['scheduled', 'rescheduled', 'confirmed', 'attended', 'completed']);
            })
            ->get()
            ->map(function ($doctor) use ($period) {
                $appointments = Appointment::whereHas('calendar', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                })
                ->whereBetween('starts_at', [$period['start'], $period['end']])
                ->whereIn('status', ['scheduled', 'rescheduled', 'confirmed', 'attended', 'completed'])
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
                    'avatar' => $doctor->user->avatar ?? null,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }
}

