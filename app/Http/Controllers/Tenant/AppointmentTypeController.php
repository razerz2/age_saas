<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('tenant')->user();
        $query = AppointmentType::with(['doctor.user']);

        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $query->whereIn('doctor_id', $allowedDoctorIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filtro opcional por médico
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointmentTypes = $query->orderBy('name')->paginate(20)->withQueryString();

        // Buscar médicos para o filtro
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            });

        // Aplicar filtros baseado no role para o filtro de médicos
        if ($user->role === 'doctor' && $user->doctor) {
            $doctorsQuery->where('id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $doctorsQuery->whereIn('id', $allowedDoctorIds);
            } else {
                $doctorsQuery->whereRaw('1 = 0');
            }
        }

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.appointment-types.index', compact('appointmentTypes', 'doctors'));
    }

    public function create()
    {
        $user = Auth::guard('tenant')->user();
        $doctorsQuery = Doctor::with('user')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->whereDoesntHave('appointmentTypes');

        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            $doctorsQuery->where('id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $doctorsQuery->whereIn('id', $allowedDoctorIds);
            } else {
                $doctorsQuery->whereRaw('1 = 0');
            }
        }

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
