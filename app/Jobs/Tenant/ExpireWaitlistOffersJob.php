<?php

namespace App\Jobs\Tenant;

use App\Models\Platform\Tenant;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Services\Tenant\WaitlistService;
use Illuminate\Support\Facades\Log;

class ExpireWaitlistOffersJob
{
    /**
     * Expira ofertas de waitlist vencidas e tenta ofertar para o próximo da fila.
     *
     * @return array{tenants:int, expired:int, offered:int, errors:int}
     */
    public function handle(): array
    {
        $totals = [
            'tenants' => 0,
            'expired' => 0,
            'offered' => 0,
            'errors' => 0,
        ];

        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);
                $totals['tenants']++;

                $expiredOffers = AppointmentWaitlistEntry::query()
                    ->where('status', AppointmentWaitlistEntry::STATUS_OFFERED)
                    ->whereNotNull('offer_expires_at')
                    ->where('offer_expires_at', '<=', now())
                    ->get(['id', 'doctor_id', 'starts_at', 'ends_at']);

                if ($expiredOffers->isEmpty()) {
                    continue;
                }

                $expiredCount = AppointmentWaitlistEntry::query()
                    ->whereIn('id', $expiredOffers->pluck('id'))
                    ->where('status', AppointmentWaitlistEntry::STATUS_OFFERED)
                    ->update([
                        'status' => AppointmentWaitlistEntry::STATUS_EXPIRED,
                    ]);

                $totals['expired'] += $expiredCount;

                $waitlistService = app(WaitlistService::class);
                $slots = $expiredOffers
                    ->map(fn (AppointmentWaitlistEntry $entry) => [
                        'doctor_id' => $entry->doctor_id,
                        'starts_at' => $entry->starts_at,
                        'ends_at' => $entry->ends_at,
                    ])
                    ->unique(fn (array $slot) => implode('|', [
                        $slot['doctor_id'],
                        optional($slot['starts_at'])->format('Y-m-d H:i:s'),
                        optional($slot['ends_at'])->format('Y-m-d H:i:s'),
                    ]))
                    ->values();

                foreach ($slots as $slot) {
                    $offeredEntry = $waitlistService->offerNext(
                        $slot['doctor_id'],
                        $slot['starts_at'],
                        $slot['ends_at']
                    );

                    if ($offeredEntry) {
                        $totals['offered']++;
                    }
                }
            } catch (\Throwable $e) {
                $totals['errors']++;

                Log::error('Erro ao expirar ofertas de waitlist', [
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

