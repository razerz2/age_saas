<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentTypeController extends Controller
{
    use HasDoctorFilter;
    public function index(Request $request)
    {
        $query = AppointmentType::with(['doctor.user']);

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        // Filtro opcional por médico (apenas se admin)
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'admin' && $request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointmentTypes = $query->orderBy('name')->paginate(20)->withQueryString();

        // Buscar médicos para o filtro
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            });

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.appointment-types.index', compact('appointmentTypes', 'doctors'));
    }

    public function create()
    {
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->whereDoesntHave('appointmentTypes');

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

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
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->where(function($query) use ($appointmentType) {
                $query->whereDoesntHave('appointmentTypes')
                      ->orWhere('id', $appointmentType->doctor_id);
            });
        
        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);
        
        $doctors = $doctorsQuery->orderBy('id')->get();

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
