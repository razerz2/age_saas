<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\MedicalSpecialtyCatalog;
use App\Http\Requests\MedicalSpecialtyCatalogRequest;

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

    public function store(MedicalSpecialtyCatalogRequest $request)
    {
        $validated = $request->validate();

        MedicalSpecialtyCatalog::create($validated);

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade cadastrada com sucesso.');
    }

    public function show(MedicalSpecialtyCatalog $medical_specialties_catalog)
    {
        return view('platform.medical_specialties_catalog.show', compact('medical_specialties_catalog'));
    }

    public function edit(MedicalSpecialtyCatalog $medical_specialties_catalog)
    {
        return view('platform.medical_specialties_catalog.create', compact('medical_specialties_catalog'));
    }

    public function update(MedicalSpecialtyCatalogRequest $request, MedicalSpecialtyCatalog $medical_specialties_catalog)
    {
        $validated = $request->validate();

        $medical_specialties_catalog->update($validated);

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade atualizada com sucesso.');
    }

    public function destroy(MedicalSpecialtyCatalog $medical_specialties_catalog)
    {
        $medical_specialties_catalog->delete();

        return redirect()
            ->route('Platform.medical_specialties_catalog.index')
            ->with('success', 'Especialidade excluída com sucesso.');
    }
}
