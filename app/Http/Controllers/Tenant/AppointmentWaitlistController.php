<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentWaitlistController extends Controller
{
    public function __construct(private readonly WaitlistService $waitlistService)
    {
    }

    public function store(Request $request, string $slug)
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'uuid', 'exists:tenant.doctors,id'],
            'patient_id' => ['required', 'uuid', 'exists:tenant.patients,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        try {
            $result = $this->waitlistService->joinWaitlist($validated);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Não foi possível entrar na fila de espera.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'message' => $result['created']
                ? 'Paciente adicionado à fila de espera.'
                : 'Paciente já está na fila de espera para este horário.',
            'created' => $result['created'],
            'slot_status' => $result['slot_status'],
            'entry' => [
                'id' => $result['entry']->id,
                'status' => $result['entry']->status,
                'starts_at' => optional($result['entry']->starts_at)->toDateTimeString(),
                'ends_at' => optional($result['entry']->ends_at)->toDateTimeString(),
            ],
        ], $result['created'] ? 201 : 200);
    }
}

