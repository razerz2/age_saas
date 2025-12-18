<?php

namespace App\Services\Network;

use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use Carbon\Carbon;

class NetworkAppointmentStatsService
{
    /**
     * Retorna estatísticas agregadas de agendamentos
     *
     * @param ClinicNetwork $network
     * @param string|null $startDate Data de início (Y-m-d)
     * @param string|null $endDate Data de fim (Y-m-d)
     * @return array
     */
    public function getStats(ClinicNetwork $network, ?string $startDate = null, ?string $endDate = null): array
    {
        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->get();

        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        $total = 0;
        $bySpecialty = [];
        $byClinic = [];
        $byStatus = [];

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                    ->with(['specialty', 'calendar.doctor'])
                    ->get();

                $clinicTotal = $appointments->count();
                $total += $clinicTotal;

                // Por especialidade
                foreach ($appointments as $appointment) {
                    if ($appointment->specialty) {
                        $specialtyId = $appointment->specialty->id;
                        $specialtyName = $appointment->specialty->name;
                        
                        if (!isset($bySpecialty[$specialtyId])) {
                            $bySpecialty[$specialtyId] = [
                                'id' => $specialtyId,
                                'name' => $specialtyName,
                                'count' => 0,
                            ];
                        }
                        $bySpecialty[$specialtyId]['count']++;
                    }

                    // Por status
                    $status = $appointment->status ?? 'unknown';
                    if (!isset($byStatus[$status])) {
                        $byStatus[$status] = 0;
                    }
                    $byStatus[$status]++;
                }

                // Por clínica
                if ($clinicTotal > 0) {
                    $byClinic[] = [
                        'tenant_id' => $tenant->id,
                        'tenant_slug' => $tenant->subdomain,
                        'tenant_name' => $tenant->trade_name ?? $tenant->legal_name,
                        'count' => $clinicTotal,
                    ];
                }

            } finally {
                tenancy()->end();
            }
        }

        // Ordena por especialidade (mais agendamentos primeiro)
        usort($bySpecialty, fn($a, $b) => $b['count'] <=> $a['count']);

        // Ordena por clínica (mais agendamentos primeiro)
        usort($byClinic, fn($a, $b) => $b['count'] <=> $a['count']);

        return [
            'total' => $total,
            'by_specialty' => array_values($bySpecialty),
            'by_clinic' => $byClinic,
            'by_status' => $byStatus,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Retorna estatísticas de um mês específico
     *
     * @param ClinicNetwork $network
     * @param int $month
     * @param int $year
     * @return int Total de agendamentos
     */
    public function getMonthStats(ClinicNetwork $network, int $month, int $year): int
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $stats = $this->getStats($network, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        return $stats['total'] ?? 0;
    }
}

