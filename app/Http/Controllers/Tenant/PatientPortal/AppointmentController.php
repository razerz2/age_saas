<?php

namespace App\Http\Controllers\Tenant\PatientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('tenant.patient_portal.appointments.index');
    }

    public function create()
    {
        return view('tenant.patient_portal.appointments.create');
    }

    public function store(Request $request)
    {
        // Implementação real de agendamento pode ser adicionada depois.
        return redirect()
            ->route('patient.appointments.index', ['slug' => $request->route('slug')])
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function edit($slug, $id)
    {
        // Implementação real de busca do agendamento pode ser adicionada depois.
        return view('tenant.patient_portal.appointments.edit', [
            'appointmentId' => $id,
        ]);
    }

    public function update(Request $request, $slug, $id)
    {
        // Implementação real de atualização pode ser adicionada depois.
        return redirect()
            ->route('patient.appointments.index', ['slug' => $slug])
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function cancel(Request $request, $slug, $id)
    {
        // Implementação real de cancelamento pode ser adicionada depois.
        return redirect()
            ->route('patient.appointments.index', ['slug' => $slug])
            ->with('success', 'Agendamento cancelado com sucesso.');
    }
}
