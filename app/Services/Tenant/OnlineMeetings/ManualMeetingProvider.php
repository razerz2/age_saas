<?php

namespace App\Services\Tenant\OnlineMeetings;

use App\Contracts\Tenant\OnlineMeetingProviderInterface;
use App\DTO\Tenant\OnlineMeetingResult;
use App\Models\Tenant\Appointment;
use App\Support\Tenant\OnlineMeeting;

class ManualMeetingProvider implements OnlineMeetingProviderInterface
{
    public function createForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        return OnlineMeetingResult::manualRequired(
            provider: $this->key(),
            errorMessage: 'Geracao manual de reuniao necessaria.',
            meta: [
                'appointment_id' => $appointment->id,
            ]
        );
    }

    public function updateForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        return OnlineMeetingResult::manualRequired(
            provider: $this->key(),
            errorMessage: 'Atualizacao manual de reuniao necessaria.',
            meta: [
                'appointment_id' => $appointment->id,
            ]
        );
    }

    public function cancelForAppointment(Appointment $appointment): OnlineMeetingResult
    {
        return OnlineMeetingResult::success(
            provider: $this->key(),
            status: OnlineMeeting::STATUS_CANCELLED,
            meta: [
                'appointment_id' => $appointment->id,
                'cancelled_manually' => true,
            ]
        );
    }

    public function key(): string
    {
        return OnlineMeeting::PROVIDER_MANUAL;
    }

    public function label(): string
    {
        return 'Manual';
    }
}

