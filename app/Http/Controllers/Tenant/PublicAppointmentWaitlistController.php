<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Tenant\AppointmentWaitlistEntry;
use App\Services\Tenant\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PublicAppointmentWaitlistController extends Controller
{
    public function __construct(private readonly WaitlistService $waitlistService)
    {
    }

    public function store(Request $request, string $tenant)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        $tenantModel->makeCurrent();

        $patientId = Session::get('public_patient_id') ?? Session::get('last_appointment_patient_id');
        if (!$patientId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Paciente não identificado.',
                ], 401);
            }

            return redirect()->route('public.patient.identify', ['slug' => $tenant])
                ->with('error', 'Por favor, identifique-se para entrar na fila de espera.');
        }

        $validated = $request->validate([
            'doctor_id' => ['required', 'uuid', 'exists:tenant.doctors,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        try {
            $result = $this->waitlistService->joinWaitlist([
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => $patientId,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Não foi possível entrar na fila de espera.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result['created']
                    ? 'Você entrou na fila de espera para este horário.'
                    : 'Você já está na fila de espera para este horário.',
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

        return back()->with('success', 'Você entrou na fila de espera para este horário.');
    }

    public function showOffer(Request $request, string $tenant, string $token)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        $tenantModel->makeCurrent();

        $entry = AppointmentWaitlistEntry::query()
            ->with('doctor.user')
            ->where('offer_token', $token)
            ->firstOrFail();

        $isOfferValid = $entry->isOfferValid();

        return view('tenant.public.appointment-offer', [
            'tenant' => $tenantModel,
            'entry' => $entry,
            'isOfferValid' => $isOfferValid,
        ]);
    }

    public function acceptOffer(Request $request, string $tenant, string $token)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();
        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        $tenantModel->makeCurrent();

        try {
            $result = $this->waitlistService->acceptOfferByToken($token, 'public');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Não foi possível confirmar a vaga.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->route('public.waitlist.offer.show', ['slug' => $tenant, 'token' => $token])
                ->withErrors($e->errors());
        }

        $appointment = $result['appointment'];

        Session::put('last_appointment_id', $appointment->id);
        Session::put('last_appointment_patient_id', $appointment->patient_id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vaga confirmada com sucesso.',
                'appointment_id' => $appointment->id,
            ]);
        }

        return redirect()->route('public.appointment.show', [
            'slug' => $tenant,
            'appointment_id' => $appointment->id,
        ])->with('success', 'Vaga confirmada com sucesso.');
    }
}

