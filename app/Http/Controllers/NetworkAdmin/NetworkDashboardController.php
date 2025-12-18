<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use App\Services\Network\NetworkDoctorAggregatorService;
use App\Services\Network\NetworkAppointmentStatsService;

class NetworkDashboardController extends Controller
{
    protected NetworkDoctorAggregatorService $doctorAggregator;
    protected NetworkAppointmentStatsService $appointmentStats;

    public function __construct(
        NetworkDoctorAggregatorService $doctorAggregator,
        NetworkAppointmentStatsService $appointmentStats
    ) {
        $this->doctorAggregator = $doctorAggregator;
        $this->appointmentStats = $appointmentStats;
    }

    /**
     * Dashboard da rede
     */
    public function index()
    {
        $network = app('currentNetwork');

        // KPIs agregados
        $tenants = $network->tenants()->where('status', 'active')->get();
        
        // Total de clínicas
        $totalClinics = $tenants->count();

        // Total de médicos (agregado)
        $doctors = $this->doctorAggregator->aggregateDoctors($network);
        $totalDoctors = $doctors->count();

        // Estatísticas de agendamentos
        $appointmentStats = $this->appointmentStats->getStats($network);

        // Crescimento mensal (agendamentos do mês atual vs mês anterior)
        $currentMonth = $this->appointmentStats->getMonthStats($network, now()->month, now()->year);
        $lastMonth = $this->appointmentStats->getMonthStats($network, now()->subMonth()->month, now()->subMonth()->year);
        $growthRate = $lastMonth > 0 
            ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2)
            : 0;

        return view('network-admin.dashboard', [
            'network' => $network,
            'totalClinics' => $totalClinics,
            'totalDoctors' => $totalDoctors,
            'totalAppointments' => $appointmentStats['total'] ?? 0,
            'appointmentsThisMonth' => $currentMonth,
            'appointmentsLastMonth' => $lastMonth,
            'growthRate' => $growthRate,
            'statsBySpecialty' => $appointmentStats['by_specialty'] ?? [],
            'statsByClinic' => $appointmentStats['by_clinic'] ?? [],
        ]);
    }
}

