<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreBusinessHourRequest;
use App\Http\Requests\Tenant\UpdateBusinessHourRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BusinessHourController extends Controller
{
    use HasDoctorFilter;
    public function index()
    {
        $query = BusinessHour::with('doctor.user');

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        $businessHours = $query->orderBy('weekday')
            ->orderBy('start_time')
            ->paginate(20);

        return view('tenant.business-hours.index', compact('businessHours'));
    }

    public function create()
    {
        $doctorsQuery = Doctor::with('user');

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.business-hours.create', compact('doctors'));
    }

    public function store(StoreBusinessHourRequest $request)
    {
        $user = Auth::guard('tenant')->user();
        
        // Determinar qual médico será usado
        $doctor = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            $doctor = $user->doctor;
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
            } elseif ($request->has('doctor_id')) {
                // Se houver múltiplos médicos, usar o doctor_id do request (admin ou usuário com múltiplos médicos)
                $doctor = Doctor::find($request->doctor_id);
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } elseif ($user->role === 'admin' && $request->has('doctor_id')) {
            // Admin pode especificar o médico
            $doctor = Doctor::find($request->doctor_id);
        }
        
        if (!$doctor) {
            return redirect()->back()->with('error', 'Médico não encontrado.');
        }
        
        $data = $request->validated();
        $weekdays = $data['weekdays'];
        $doctorId = $doctor->id;
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
                    'break_start_time' => $data['break_start_time'] ?? null,
                    'break_end_time' => $data['break_end_time'] ?? null,
                ]);
                $createdCount++;
            }
        }

        $message = $createdCount > 0 
            ? "Horário de atendimento criado com sucesso para {$createdCount} dia(s)."
            : "Nenhum horário foi criado. Os horários selecionados já existem.";

        return redirect()->route('tenant.business-hours.index', ['slug' => tenant()->subdomain])
            ->with('success', $message);
    }

    public function show($slug, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->load('doctor.user');

        return view('tenant.business-hours.show', compact('businessHour'));
    }

    public function edit($slug, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        
        $doctorsQuery = Doctor::with('user');
        
        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);
        
        $doctors = $doctorsQuery->orderBy('id')->get();
        $businessHour->load('doctor');

        return view('tenant.business-hours.edit', compact('businessHour', 'doctors'));
    }

    public function update(UpdateBusinessHourRequest $request, $slug, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->update($request->validated());

        return redirect()->route('tenant.business-hours.index', ['slug' => $slug])
            ->with('success', 'Horário atualizado com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $businessHour = BusinessHour::findOrFail($id);
        $businessHour->delete();

        return redirect()->route('tenant.business-hours.index', ['slug' => $slug])
            ->with('success', 'Horário removido.');
    }
}
