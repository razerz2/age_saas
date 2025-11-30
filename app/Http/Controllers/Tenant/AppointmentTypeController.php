<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AppointmentTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = AppointmentType::with(['doctor.user'])->orderBy('name');

        // Filtro opcional por médico
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointmentTypes = $query->paginate(20)->withQueryString();

        // Buscar médicos para o filtro
        $doctors = Doctor::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('id')
            ->get();

        return view('tenant.appointment-types.index', compact('appointmentTypes', 'doctors'));
    }

    public function create()
    {
        // Listar médicos ativos (com status active) que ainda não possuem tipo de consulta
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->whereDoesntHave('appointmentTypes')
            ->orderBy('id')
            ->get();

        return view('tenant.appointment-types.create', compact('doctors'));
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
        $appointmentType = AppointmentType::with(['doctor.user'])->findOrFail($id);
        return view('tenant.appointment-types.show', compact('appointmentType'));
    }

    public function edit($id)
    {
        $appointmentType = AppointmentType::with(['doctor.user'])->findOrFail($id);

        // Listar médicos ativos (com status active) que ainda não possuem tipo de consulta
        // ou o médico atual deste tipo de consulta (para permitir edição)
        $doctors = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->where(function($query) use ($appointmentType) {
                $query->whereDoesntHave('appointmentTypes')
                      ->orWhere('id', $appointmentType->doctor_id);
            })
            ->orderBy('id')
            ->get();

        return view('tenant.appointment-types.edit', compact('appointmentType', 'doctors'));
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
