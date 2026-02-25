<?php

namespace App\Jobs\Tenant;

use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use App\Services\Tenant\NotificationDispatcher;
use App\Services\Tenant\WaitlistService;
use Illuminate\Support\Facades\Log;

class ExpirePendingAppointmentsJob
{
    /**
     * Expira agendamentos em hold cujo prazo de confirmação venceu.
     *
     * @return array{tenants:int, expired:int, errors:int}
     */
    public function handle(): array
    {
        $totals = [
            'tenants' => 0,
            'expired' => 0,
            'errors' => 0,
        ];

        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);
                $totals['tenants']++;

                $pendingAppointments = Appointment::query()
                    ->where('status', 'pending_confirmation')
                    ->whereNotNull('confirmation_expires_at')
                    ->where('confirmation_expires_at', '<=', now())
                    ->get(['id', 'doctor_id', 'starts_at', 'ends_at']);

                if ($pendingAppointments->isEmpty()) {
                    continue;
                }

                $expiredCount = Appointment::query()
                    ->whereIn('id', $pendingAppointments->pluck('id'))
                    ->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                        'confirmation_expires_at' => null,
                    ]);

                $totals['expired'] += $expiredCount;

                $dispatcher = app(NotificationDispatcher::class);
                $expiredAppointments = Appointment::query()
                    ->whereIn('id', $pendingAppointments->pluck('id'))
                    ->with([
                        'patient',
                        'doctor.user',
                        'doctor.specialties',
                        'calendar.doctor.user',
                        'specialty',
                        'type',
                    ])
                    ->get();

                foreach ($expiredAppointments as $expiredAppointment) {
                    $dispatcher->dispatchAppointment(
                        $expiredAppointment,
                        'appointment.expired',
                        ['event' => 'appointment_expired_by_job']
                    );
                }

                $waitlistService = app(WaitlistService::class);
                $releasedSlots = $pendingAppointments
                    ->map(fn (Appointment $appointment) => [
                        'doctor_id' => $appointment->doctor_id,
                        'starts_at' => $appointment->starts_at,
                        'ends_at' => $appointment->ends_at,
                    ])
                    ->unique(fn (array $slot) => implode('|', [
                        $slot['doctor_id'],
                        optional($slot['starts_at'])->format('Y-m-d H:i:s'),
                        optional($slot['ends_at'])->format('Y-m-d H:i:s'),
                    ]))
                    ->values();

                foreach ($releasedSlots as $slot) {
                    $waitlistService->onSlotReleased(
                        $slot['doctor_id'],
                        $slot['starts_at'],
                        $slot['ends_at']
                    );
                }
            } catch (\Throwable $e) {
                $totals['errors']++;

                Log::error('Erro ao expirar agendamentos pendentes', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                tenancy()->end();
            }
        }

        return $totals;
    }
}
