<?php

namespace App\Contracts\Tenant;

use App\DTO\Tenant\OnlineMeetingResult;
use App\Models\Tenant\Appointment;

interface OnlineMeetingProviderInterface
{
    public function createForAppointment(Appointment $appointment): OnlineMeetingResult;

    public function updateForAppointment(Appointment $appointment): OnlineMeetingResult;

    public function cancelForAppointment(Appointment $appointment): OnlineMeetingResult;

    public function key(): string;

    public function label(): string;
}

