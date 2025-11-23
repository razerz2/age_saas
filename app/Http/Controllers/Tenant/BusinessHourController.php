<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreBusinessHourRequest;
use App\Http\Requests\Tenant\UpdateBusinessHourRequest;
use Illuminate\Support\Str;

class BusinessHourController extends Controller
{
    public function index()
    {
        $hours = BusinessHour::with('doctor.user')
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->paginate(20);

        return view('tenant.business_hours.index', compact('hours'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();

        return view('tenant.business_hours.create', compact('doctors'));
    }

    public function store(StoreBusinessHourRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        BusinessHour::create($data);

        return redirect()->route('tenant.business_hours.index')
            ->with('success', 'Horário de atendimento criado com sucesso.');
    }

    public function show(BusinessHour $businessHour)
    {
        $businessHour->load('doctor.user');

        return view('tenant.business_hours.show', compact('businessHour'));
    }

    public function edit(BusinessHour $businessHour)
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $businessHour->load('doctor');

        return view('tenant.business_hours.edit', compact('businessHour', 'doctors'));
    }

    public function update(UpdateBusinessHourRequest $request, BusinessHour $businessHour)
    {
        $businessHour->update($request->validated());

        return redirect()->route('tenant.business_hours.index')
            ->with('success', 'Horário atualizado com sucesso.');
    }

    public function destroy(BusinessHour $businessHour)
    {
        $businessHour->delete();

        return redirect()->route('tenant.business_hours.index')
            ->with('success', 'Horário removido.');
    }
}
