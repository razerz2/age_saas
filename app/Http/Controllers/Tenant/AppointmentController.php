<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Patient;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\AppointmentType;
use App\Http\Requests\Tenant\StoreAppointmentRequest;
use App\Http\Requests\Tenant\UpdateAppointmentRequest;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['calendar.doctor.user', 'patient', 'type', 'specialty'])
            ->orderBy('starts_at')
            ->paginate(30);

        return view('tenant.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $calendars      = Calendar::with('doctor.user')->orderBy('name')->get();
        $patients       = Patient::orderBy('full_name')->get();
        $specialties    = MedicalSpecialty::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        return view('tenant.appointments.create', compact(
            'calendars',
            'patients',
            'specialties',
            'appointmentTypes'
        ));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Appointment::create($data);

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento criado com sucesso.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['calendar.doctor.user', 'patient', 'type', 'specialty']);
        return view('tenant.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $calendars      = Calendar::with('doctor.user')->orderBy('name')->get();
        $patients       = Patient::orderBy('full_name')->get();
        $specialties    = MedicalSpecialty::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        $appointment->load(['calendar', 'patient', 'specialty', 'type']);

        return view('tenant.appointments.edit', compact(
            'appointment',
            'calendars',
            'patients',
            'specialties',
            'appointmentTypes'
        ));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('tenant.appointments.index')
            ->with('success', 'Agendamento removido.');
    }
}
