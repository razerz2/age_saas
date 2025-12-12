<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Calendar;
use App\Models\Tenant\BusinessHour;
use App\Models\Tenant\AppointmentType;
use App\Models\Tenant\Doctor;
use App\Http\Requests\Tenant\StoreCalendarRequest;
use App\Http\Requests\Tenant\UpdateCalendarRequest;
use App\Http\Requests\Tenant\StoreBusinessHourRequest;
use App\Http\Requests\Tenant\UpdateBusinessHourRequest;
use App\Http\Requests\Tenant\StoreAppointmentTypeRequest;
use App\Http\Requests\Tenant\UpdateAppointmentTypeRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DoctorSettingsController extends Controller
{
    /**
     * Exibe a página única de configurações para médico ou usuário com 1 médico relacionado
     */
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        
        // Determinar qual médico será exibido
        $doctor = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            // Médico logado vê suas próprias informações
            $doctor = $user->doctor;
        } elseif ($user->role === 'user') {
            // Usuário comum vê informações do médico relacionado
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
            } else {
                // Se tem mais de 1 médico, redireciona para as páginas separadas
                return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain]);
            }
        } else {
            // Admin ou outros casos não devem acessar esta página
            return redirect()->route('tenant.calendars.index', ['slug' => tenant()->subdomain]);
        }
        
        if (!$doctor) {
            return redirect()->route('tenant.dashboard', ['slug' => tenant()->subdomain])
                ->with('error', 'Nenhum médico encontrado.');
        }
        
        // Carregar dados do médico
        $calendar = $doctor->calendars()->first();
        $businessHours = $doctor->businessHours()->orderBy('weekday')->orderBy('start_time')->get();
        $appointmentTypes = $doctor->appointmentTypes()->orderBy('name')->get();
        
        return view('tenant.doctor-settings.index', compact('doctor', 'calendar', 'businessHours', 'appointmentTypes'));
    }
    
    /**
     * Atualiza ou cria o calendário do médico
     */
    public function updateCalendar(Request $request)
    {
        $user = Auth::guard('tenant')->user();
        
        // Determinar qual médico será atualizado
        $doctor = null;
        
        if ($user->role === 'doctor' && $user->doctor) {
            $doctor = $user->doctor;
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        if (!$doctor) {
            return redirect()->back()->with('error', 'Médico não encontrado.');
        }
        
        // Validação manual
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
        ]);
        
        $data = [
            'doctor_id' => $doctor->id,
            'name' => $request->name,
            'external_id' => $request->external_id,
        ];
        
        $calendar = $doctor->calendars()->first();
        
        if ($calendar) {
            // Atualizar calendário existente
            $calendar->update($data);
            $message = 'Calendário atualizado com sucesso.';
        } else {
            // Criar novo calendário
            $data['id'] = Str::uuid();
            Calendar::create($data);
            $message = 'Calendário criado com sucesso.';
        }
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => tenant()->subdomain])
            ->with('success', $message);
    }
    
    /**
     * Cria um novo horário comercial
     */
    public function storeBusinessHour(StoreBusinessHourRequest $request)
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
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        if (!$doctor) {
            return redirect()->back()->with('error', 'Médico não encontrado.');
        }
        
        $data = $request->validated();
        $data['doctor_id'] = $doctor->id;
        $weekdays = $data['weekdays'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        
        $createdCount = 0;
        foreach ($weekdays as $weekday) {
            // Verificar se já existe um horário para este médico, dia e horário
            $exists = BusinessHour::where('doctor_id', $doctor->id)
                ->where('weekday', $weekday)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->exists();
            
            if (!$exists) {
                BusinessHour::create([
                    'id' => Str::uuid(),
                    'doctor_id' => $doctor->id,
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
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => tenant()->subdomain])
            ->with('success', $message);
    }
    
    /**
     * Atualiza um horário comercial
     */
    public function updateBusinessHour(UpdateBusinessHourRequest $request, $slug, $id)
    {
        $user = Auth::guard('tenant')->user();
        $businessHour = BusinessHour::findOrFail($id);
        
        // Verificar permissão
        $doctor = null;
        if ($user->role === 'doctor' && $user->doctor) {
            $doctor = $user->doctor;
            if ($businessHour->doctor_id !== $doctor->id) {
                abort(403, 'Você não tem permissão para editar este horário.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
                if ($businessHour->doctor_id !== $doctor->id) {
                    abort(403, 'Você não tem permissão para editar este horário.');
                }
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        $businessHour->update($request->validated());
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => $slug])
            ->with('success', 'Horário atualizado com sucesso.');
    }
    
    /**
     * Remove um horário comercial
     */
    public function destroyBusinessHour($slug, $id)
    {
        $user = Auth::guard('tenant')->user();
        $businessHour = BusinessHour::findOrFail($id);
        
        // Verificar permissão
        if ($user->role === 'doctor' && $user->doctor) {
            if ($businessHour->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para remover este horário.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
                if ($businessHour->doctor_id !== $doctor->id) {
                    abort(403, 'Você não tem permissão para remover este horário.');
                }
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        $businessHour->delete();
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => $slug])
            ->with('success', 'Horário removido com sucesso.');
    }
    
    /**
     * Cria um novo tipo de atendimento
     */
    public function storeAppointmentType(StoreAppointmentTypeRequest $request)
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
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        if (!$doctor) {
            return redirect()->back()->with('error', 'Médico não encontrado.');
        }
        
        $data = $request->validated();
        $data['doctor_id'] = $doctor->id;
        $data['id'] = Str::uuid();
        
        AppointmentType::create($data);
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Tipo de atendimento criado com sucesso.');
    }
    
    /**
     * Atualiza um tipo de atendimento
     */
    public function updateAppointmentType(UpdateAppointmentTypeRequest $request, $slug, $id)
    {
        $user = Auth::guard('tenant')->user();
        $appointmentType = AppointmentType::findOrFail($id);
        
        // Verificar permissão
        if ($user->role === 'doctor' && $user->doctor) {
            if ($appointmentType->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para editar este tipo de atendimento.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
                if ($appointmentType->doctor_id !== $doctor->id) {
                    abort(403, 'Você não tem permissão para editar este tipo de atendimento.');
                }
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        $appointmentType->update($request->validated());
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => $slug])
            ->with('success', 'Tipo de atendimento atualizado com sucesso.');
    }
    
    /**
     * Remove um tipo de atendimento
     */
    public function destroyAppointmentType($slug, $id)
    {
        $user = Auth::guard('tenant')->user();
        $appointmentType = AppointmentType::findOrFail($id);
        
        // Verificar permissão
        if ($user->role === 'doctor' && $user->doctor) {
            if ($appointmentType->doctor_id !== $user->doctor->id) {
                abort(403, 'Você não tem permissão para remover este tipo de atendimento.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctors = $user->allowedDoctors()->get();
            if ($allowedDoctors->count() === 1) {
                $doctor = $allowedDoctors->first();
                if ($appointmentType->doctor_id !== $doctor->id) {
                    abort(403, 'Você não tem permissão para remover este tipo de atendimento.');
                }
            } else {
                abort(403, 'Você não tem permissão para realizar esta ação.');
            }
        } else {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
        
        $appointmentType->delete();
        
        return redirect()->route('tenant.doctor-settings.index', ['slug' => $slug])
            ->with('success', 'Tipo de atendimento removido com sucesso.');
    }
}

