<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\MedicalSpecialtyCatalog;
use Illuminate\Http\Request;

class MedicalSpecialtyCatalogController extends Controller
{
    public function index()
    {
        $specialties = MedicalSpecialtyCatalog::orderBy('name')->get();
        return view('platform.medical_specialties_catalog.index', compact('specialties'));
    }

    public function create()
    {
        return view('platform.medical_specialties_catalog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:medical_specialties_catalog,name',
            'code' => 'nullable|string|max:20',
        ]);

        MedicalSpecialtyCatalog::create($validated);

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade cadastrada com sucesso.');
    }

    public function show(MedicalSpecialtyCatalog $medicalSpecialtyCatalog)
    {
        return view('platform.medical_specialties_catalog.show', compact('medicalSpecialtyCatalog'));
    }

    public function edit(MedicalSpecialtyCatalog $medicalSpecialtyCatalog)
    {
        return view('platform.medical_specialties_catalog.create', compact('medicalSpecialtyCatalog'));
    }

    public function update(Request $request, MedicalSpecialtyCatalog $medicalSpecialtyCatalog)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:medical_specialties_catalog,name,' . $medicalSpecialtyCatalog->id . ',id',
            'code' => 'nullable|string|max:20',
        ]);

        $medicalSpecialtyCatalog->update($validated);

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade atualizada com sucesso.');
    }

    public function destroy(MedicalSpecialtyCatalog $medicalSpecialtyCatalog)
    {
        $medicalSpecialtyCatalog->delete();

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade excluída com sucesso.');
    }
}
