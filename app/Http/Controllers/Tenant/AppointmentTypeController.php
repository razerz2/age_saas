<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AppointmentType;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;

class AppointmentTypeController extends Controller
{
    public function index()
    {
        $types = AppointmentType::orderBy('name')->paginate(20);

        return view('tenant.appointment_types.index', compact('types'));
    }

    public function create()
    {
        return view('tenant.appointment_types.create');
    }

    public function store(StoreAppointmentTypeRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        AppointmentType::create($data);

        return redirect()->route('tenant.appointment_types.index')
            ->with('success', 'Tipo de atendimento criado com sucesso.');
    }

    public function show(AppointmentType $appointmentType)
    {
        return view('tenant.appointment_types.show', compact('appointmentType'));
    }

    public function edit(AppointmentType $appointmentType)
    {
        return view('tenant.appointment_types.edit', compact('appointmentType'));
    }

    public function update(UpdateAppointmentTypeRequest $request, AppointmentType $appointmentType)
    {
        $appointmentType->update($request->validated());

        return redirect()->route('tenant.appointment_types.index')
            ->with('success', 'Tipo de atendimento atualizado com sucesso.');
    }

    public function destroy(AppointmentType $appointmentType)
    {
        $appointmentType->delete();

        return redirect()->route('tenant.appointment_types.index')
            ->with('success', 'Tipo removido.');
    }
}
