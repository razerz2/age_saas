<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreBusinessHourRequest;
use App\Http\Requests\Tenant\UpdateBusinessHourRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BusinessHourController extends Controller
{
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        $query = BusinessHour::with('doctor.user');

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

        $businessHours = $query->orderBy('weekday')
            ->orderBy('start_time')
            ->paginate(20);

        return view('tenant.business-hours.index', compact('businessHours'));
    }

    public function create()
    {
        $user = Auth::guard('tenant')->user();
        $doctorsQuery = Doctor::with('user');

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

        return view('tenant.business-hours.create', compact('doctors'));
    }

    public function store(StoreBusinessHourRequest $request)
    {
        $data = $request->validated();
        $weekdays = $data['weekdays'];
        $doctorId = $data['doctor_id'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        $createdCount = 0;
        foreach ($weekdays as $weekday) {
            // Verificar se já existe um horário para este médico, dia e horário
            $exists = BusinessHour::where('doctor_id', $doctorId)
                ->where('weekday', $weekday)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->exists();

            if (!$exists) {
                BusinessHour::create([
                    'id' => Str::uuid(),
                    'doctor_id' => $doctorId,
                    'weekday' => $weekday,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
                $createdCount++;
            }
        }

        $message = $createdCount > 0 
            ? "Horário de atendimento criado com sucesso para {$createdCount} dia(s)."
            : "Nenhum horário foi criado. Os horários selecionados já existem.";

        return redirect()->route('tenant.business-hours.index')
            ->with('success', $message);
    }

    public function show($id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->load('doctor.user');

        return view('tenant.business-hours.show', compact('businessHour'));
    }

    public function edit($id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $businessHour->load('doctor');

        return view('tenant.business-hours.edit', compact('businessHour', 'doctors'));
    }

    public function update(UpdateBusinessHourRequest $request, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->update($request->validated());

        return redirect()->route('tenant.business-hours.index')
            ->with('success', 'Horário atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->delete();

        return redirect()->route('tenant.business-hours.index')
            ->with('success', 'Horário removido.');
    }
}
