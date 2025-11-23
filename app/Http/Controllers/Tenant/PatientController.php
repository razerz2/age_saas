<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Patient;
use App\Http\Requests\Tenant\StorePatientRequest;
use App\Http\Requests\Tenant\UpdatePatientRequest;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::orderBy('full_name')->paginate(20);

        return view('tenant.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('tenant.patients.create');
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        $data['id'] = Str::uuid();

        Patient::create($data);

        return redirect()->route('tenant.patients.index')
            ->with('success', 'Paciente cadastrado com sucesso.');
    }


    public function show(Patient $patient)
    {
        return view('tenant.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('tenant.patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return redirect()->route('tenant.patients.index')
            ->with('success', 'Paciente atualizado com sucesso.');
    }


    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('tenant.patients.index')
            ->with('success', 'Paciente removido.');
    }
}
