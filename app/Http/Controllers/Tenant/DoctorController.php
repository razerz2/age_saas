<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\User;
use App\Models\Tenant\MedicalSpecialty;
use App\Http\Requests\Tenant\StoreDoctorRequest;
use App\Http\Requests\Tenant\UpdateDoctorRequest;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['user', 'specialties'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('tenant.doctors.index', compact('doctors'));
    }

    public function create()
    {
        // Buscar apenas usuários que não possuem vínculo com médico
        $users = User::whereDoesntHave('doctor')
            ->orderBy('name')
            ->get();
        
        $specialties = MedicalSpecialty::orderBy('name')->get();

        return view('tenant.doctors.create', compact('users', 'specialties'));
    }

    public function store(StoreDoctorRequest $request)
    {
        $data = $request->validated();

        /** @var Doctor $doctor */
        $doctor = Doctor::create([
            'id'        => \Str::uuid(),
            'user_id'   => $data['user_id'],
            'crm_number' => $data['crm_number'] ?? null,
            'crm_state' => $data['crm_state'] ?? null,
            'signature' => $data['signature'] ?? null,
        ]);

        if (!empty($data['specialties'])) {
            $doctor->specialties()->sync($data['specialties']);
        }

        return redirect()->route('tenant.doctors.index')
            ->with('success', 'Médico cadastrado com sucesso.');
    }

    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->load(['user', 'specialties']);
        return view('tenant.doctors.show', compact('doctor'));
    }

    public function edit($id)
    {
        $doctor = Doctor::findOrFail($id);
        
        // Buscar usuários que não possuem vínculo com médico, 
        // OU o usuário atual deste médico (para permitir manter o mesmo usuário)
        $users = User::where(function ($query) use ($doctor) {
            $query->whereDoesntHave('doctor')
                  ->orWhere('id', $doctor->user_id);
        })->orderBy('name')->get();
        
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $doctor->load('specialties');

        return view('tenant.doctors.edit', compact('doctor', 'users', 'specialties'));
    }

    public function update(UpdateDoctorRequest $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $data = $request->validated();

        $doctor->update([
            'user_id'   => $data['user_id'],
            'crm_number' => $data['crm_number'] ?? null,
            'crm_state' => $data['crm_state'] ?? null,
            'signature' => $data['signature'] ?? null,
        ]);

        if (!empty($data['specialties'])) {
            $doctor->specialties()->sync($data['specialties']);
        } else {
            $doctor->specialties()->detach();
        }

        return redirect()->route('tenant.doctors.index')
            ->with('success', 'Médico atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);

        // Verificar se o médico possui atendimentos
        if ($doctor->hasAppointments()) {
            return redirect()->route('tenant.doctors.index')
                ->with('error', 'Não é possível excluir o médico pois ele possui atendimentos cadastrados.');
        }

        $doctor->delete();

        return redirect()->route('tenant.doctors.index')
            ->with('success', 'Médico removido com sucesso.');
    }
}
