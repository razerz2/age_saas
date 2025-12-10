<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MedicalSpecialty;
use App\Http\Requests\Tenant\StoreMedicalSpecialtyRequest;
use App\Http\Requests\Tenant\UpdateMedicalSpecialtyRequest;

class MedicalSpecialtyController extends Controller
{
    public function index()
    {
        $specialties = MedicalSpecialty::orderBy('name')->paginate(20);

        return view('tenant.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('tenant.specialties.create');
    }

    public function store(StoreMedicalSpecialtyRequest $request)
    {
        MedicalSpecialty::create([
            'id'   => \Str::uuid(),
            'name' => $request->validated()['name'],
            'code' => $request->validated()['code'] ?? null,
        ]);

        return redirect()->route('tenant.specialties.index')
            ->with('success', 'Especialidade cadastrada com sucesso.');
    }

    public function show($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        return view('tenant.specialties.show', compact('specialty'));
    }

    public function edit($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        return view('tenant.specialties.edit', compact('specialty'));
    }

    public function update(UpdateMedicalSpecialtyRequest $request, $slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        $specialty->update($request->validated());

        return redirect()->route('tenant.specialties.index')
            ->with('success', 'Especialidade atualizada com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        $specialty->delete();

        return redirect()->route('tenant.specialties.index')
            ->with('success', 'Especialidade removida.');
    }
}
