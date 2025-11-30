<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreCalendarRequest;
use App\Http\Requests\Tenant\UpdateCalendarRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        
        $query = Calendar::with('doctor.user');
        
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
        
        $calendars = $query->orderBy('name')->paginate(20);

        return view('tenant.calendars.index', compact('calendars'));
    }

    public function create()
    {
        $user = Auth::guard('tenant')->user();
        
        // Busca médicos que ainda não possuem calendário
        $doctors = Doctor::with('user')
            ->whereDoesntHave('calendars')
            ->orderBy('id')
            ->get();
        
        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            if ($user->doctor->calendars()->exists()) {
                return redirect()->route('tenant.calendars.index')
                    ->with('error', 'Você já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.');
            }
            $doctors = $doctors->where('id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $doctors = $doctors->whereIn('id', $allowedDoctorIds);
            } else {
                $doctors = collect();
            }
        }

        return view('tenant.calendars.create', compact('doctors'));
    }

    public function store(StoreCalendarRequest $request)
    {
        $user = Auth::guard('tenant')->user();
        $data = $request->validated();
        
        // Verifica se o médico já possui um calendário
        $doctor = Doctor::findOrFail($data['doctor_id']);
        if ($doctor->calendars()->exists()) {
            return redirect()->route('tenant.calendars.create')
                ->with('error', 'Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.')
                ->withInput();
        }
        
        // Se o usuário é médico, verifica se está tentando criar para outro médico
        if ($user->role === 'doctor' && $user->doctor) {
            if ($data['doctor_id'] !== $user->doctor->id) {
                abort(403, 'Você só pode criar calendário para si mesmo.');
            }
            // Verifica se já tem calendário
            if ($user->doctor->calendars()->exists()) {
                return redirect()->route('tenant.calendars.index')
                    ->with('error', 'Você já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.');
            }
        }
        
        $data['id'] = Str::uuid();
        Calendar::create($data);

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda criada com sucesso.');
    }

    public function show($id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor.user');
        
        $user = Auth::guard('tenant')->user();
        
        // Verifica permissão para visualizar o calendário
        if ($user->role === 'doctor' && $user->doctor) {
            if ($calendar->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para visualizar este calendário.');
            }
        } elseif ($user->role === 'user') {
            if (!$user->belongsToUser($calendar->doctor_id)) {
                abort(403, 'Você não tem permissão para visualizar este calendário.');
            }
        }

        return view('tenant.calendars.show', compact('calendar'));
    }

    public function edit($id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->load('doctor');
        
        $user = Auth::guard('tenant')->user();
        
        // Verifica permissão para editar o calendário
        if ($user->role === 'doctor' && $user->doctor) {
            if ($calendar->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para editar este calendário.');
            }
        } elseif ($user->role === 'user') {
            if (!$user->belongsToUser($calendar->doctor_id)) {
                abort(403, 'Você não tem permissão para editar este calendário.');
            }
        }
        
        // Busca médicos que não possuem calendário OU o médico do calendário atual (para permitir edição)
        $doctors = Doctor::with('user')
            ->where(function ($query) use ($calendar) {
                $query->whereDoesntHave('calendars')
                      ->orWhere('id', $calendar->doctor_id);
            })
            ->orderBy('id')
            ->get();

        return view('tenant.calendars.edit', compact('calendar', 'doctors'));
    }

    public function update(UpdateCalendarRequest $request, $id)
    {
        $calendar = Calendar::findOrFail($id);
        
        $user = Auth::guard('tenant')->user();
        $data = $request->validated();
        
        // Verifica permissão para atualizar o calendário
        if ($user->role === 'doctor' && $user->doctor) {
            if ($calendar->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para atualizar este calendário.');
            }
            // Médico não pode mudar o médico do calendário
            if ($data['doctor_id'] !== $calendar->doctor_id) {
                return redirect()->route('tenant.calendars.edit', $calendar->id)
                    ->with('error', 'Você não pode alterar o médico do calendário.')
                    ->withInput();
            }
        } elseif ($user->role === 'user') {
            if (!$user->belongsToUser($calendar->doctor_id)) {
                abort(403, 'Você não tem permissão para atualizar este calendário.');
            }
        }
        
        // Se está tentando mudar o médico, verifica se o novo médico já tem calendário
        if ($data['doctor_id'] !== $calendar->doctor_id) {
            $newDoctor = Doctor::findOrFail($data['doctor_id']);
            if ($newDoctor->calendars()->where('id', '!=', $calendar->id)->exists()) {
                return redirect()->route('tenant.calendars.edit', $calendar->id)
                    ->with('error', 'Este médico já possui um calendário cadastrado. Cada médico pode ter apenas um calendário.')
                    ->withInput();
            }
        }
        
        $calendar->update($data);

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $calendar = Calendar::findOrFail($id);
        
        $user = Auth::guard('tenant')->user();
        
        // Verifica permissão para remover o calendário
        if ($user->role === 'doctor' && $user->doctor) {
            if ($calendar->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para remover este calendário.');
            }
        } elseif ($user->role === 'user') {
            if (!$user->belongsToUser($calendar->doctor_id)) {
                abort(403, 'Você não tem permissão para remover este calendário.');
            }
        }
        
        $calendar->delete();

        return redirect()->route('tenant.calendars.index')
            ->with('success', 'Agenda removida.');
    }

    public function eventsRedirect()
    {
        $user = Auth::guard('tenant')->user();
        
        $query = Calendar::orderBy('name');
        
        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            $calendar = $query->where('doctor_id', $user->doctor->id)->first();
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $calendar = $query->whereIn('doctor_id', $allowedDoctorIds)->first();
            }
        } else {
            $calendar = $query->first();
        }

        if ($calendar) {
            return redirect()->route('tenant.calendars.events', $calendar->id);
        }

        return redirect()->route('tenant.calendars.index')
            ->with('info', 'Nenhum calendário encontrado. Crie um calendário primeiro.');
    }
}
