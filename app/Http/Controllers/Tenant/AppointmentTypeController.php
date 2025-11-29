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
        $appointmentTypes = AppointmentType::orderBy('name')->paginate(20);

        return view('tenant.appointment-types.index', compact('appointmentTypes'));
    }

    public function create()
    {
        return view('tenant.appointment-types.create');
    }

    public function store(StoreAppointmentTypeRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        AppointmentType::create($data);

        return redirect()->route('tenant.appointment-types.index')
            ->with('success', 'Tipo de atendimento criado com sucesso.');
    }

    public function show($id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        return view('tenant.appointment-types.show', compact('appointmentType'));
    }

    public function edit($id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        return view('tenant.appointment-types.edit', compact('appointmentType'));
    }

    public function update(UpdateAppointmentTypeRequest $request, $id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        $appointmentType->update($request->validated());

        return redirect()->route('tenant.appointment-types.index')
            ->with('success', 'Tipo de atendimento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        $appointmentType->delete();

        return redirect()->route('tenant.appointment-types.index')
            ->with('success', 'Tipo removido.');
    }
}
