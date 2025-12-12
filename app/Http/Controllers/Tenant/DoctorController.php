<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\User;
use App\Models\Tenant\MedicalSpecialty;
use App\Http\Requests\Tenant\StoreDoctorRequest;
use App\Http\Requests\Tenant\UpdateDoctorRequest;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        $query = Doctor::with(['user', 'specialties']);

        // Aplicar filtros baseado no role
        if ($user->role === 'doctor' && $user->doctor) {
            // Médico só vê a si mesmo
            $query->where('id', $user->doctor->id);
        } elseif ($user->role === 'user') {
            // Usuário comum só vê médicos relacionados
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                $query->whereIn('id', $allowedDoctorIds);
            } else {
                // Se não tem médicos permitidos, não mostra nada
                $query->whereRaw('1 = 0');
            }
        }
        // Admin vê tudo (sem filtro)

        $doctors = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('tenant.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $loggedUser = Auth::guard('tenant')->user();
        
        // Base: apenas usuários médicos que não possuem vínculo com médico
        $query = User::where('role', 'doctor')
            ->whereDoesntHave('doctor');
        
        // Aplicar filtros baseado no role do usuário logado
        if ($loggedUser->role === 'admin') {
            // Admin: lista todos os usuários médicos
            // (sem filtro adicional)
        } elseif ($loggedUser->role === 'user') {
            // Usuário comum: lista apenas usuários médicos cujos médicos têm relação com ele
            $allowedDoctorIds = $loggedUser->allowedDoctors()->pluck('doctors.id')->toArray();
            if (!empty($allowedDoctorIds)) {
                // Buscar os user_id dos médicos relacionados
                $relatedUserIds = Doctor::whereIn('id', $allowedDoctorIds)
                    ->pluck('user_id')
                    ->toArray();
                // Filtrar apenas esses usuários médicos
                $query->whereIn('id', $relatedUserIds);
            } else {
                // Se não tem médicos permitidos, não mostra nada
                $query->whereRaw('1 = 0');
            }
        } elseif ($loggedUser->role === 'doctor' && $loggedUser->doctor) {
            // Usuário médico: lista apenas usuários médicos cujos médicos têm relação com ele
            $allowedDoctorIds = $loggedUser->allowedDoctors()->pluck('doctors.id')->toArray();
            // Incluir também o próprio médico
            $allowedDoctorIds[] = $loggedUser->doctor->id;
            $allowedDoctorIds = array_unique($allowedDoctorIds);
            
            if (!empty($allowedDoctorIds)) {
                // Buscar os user_id dos médicos relacionados
                $relatedUserIds = Doctor::whereIn('id', $allowedDoctorIds)
                    ->pluck('user_id')
                    ->toArray();
                // Incluir também ele mesmo (caso não tenha médico ainda)
                $relatedUserIds[] = $loggedUser->id;
                $relatedUserIds = array_unique($relatedUserIds);
                // Filtrar apenas esses usuários médicos
                $query->whereIn('id', $relatedUserIds);
            } else {
                // Se não tem médicos permitidos, só mostra ele mesmo
                $query->where('id', $loggedUser->id);
            }
        }
        
        $users = $query->orderBy('name')->get();
        $specialties = MedicalSpecialty::orderBy('name')->get();

        return view('tenant.doctors.create', compact('users', 'specialties'));
    }

    public function store(StoreDoctorRequest $request)
    {
        $data = $request->validated();
        $user = Auth::guard('tenant')->user();

        /** @var Doctor $doctor */
        $doctor = Doctor::create([
            'id'        => \Str::uuid(),
            'user_id'   => $data['user_id'],
            'crm_number' => $data['crm_number'] ?? null,
            'crm_state' => $data['crm_state'] ?? null,
            'signature' => $data['signature'] ?? null,
            'label_singular' => $data['label_singular'] ?? null,
            'label_plural' => $data['label_plural'] ?? null,
            'registration_label' => $data['registration_label'] ?? null,
            'registration_value' => $data['registration_value'] ?? null,
        ]);

        if (!empty($data['specialties'])) {
            $doctor->specialties()->sync($data['specialties']);
        }

        // Se o usuário que cadastrou é role 'user', vincular automaticamente
        if ($user->role === 'user') {
            \App\Models\Tenant\UserDoctorPermission::create([
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
            ]);
        }

        $slug = $request->route('slug') ?? tenant()->subdomain;
        
        return redirect()->route('tenant.doctors.index', ['slug' => $slug])
            ->with('success', 'Médico cadastrado com sucesso.');
    }

    public function show($slug, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->load(['user', 'specialties']);
        return view('tenant.doctors.show', compact('doctor'));
    }

    public function edit($slug, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $loggedUser = Auth::guard('tenant')->user();
        
        // Base: apenas usuários médicos que não possuem vínculo com médico, 
        // OU o usuário atual deste médico (para permitir manter o mesmo usuário)
        $query = User::where('role', 'doctor')
            ->where(function ($query) use ($doctor) {
                $query->whereDoesntHave('doctor')
                      ->orWhere('id', $doctor->user_id);
            });
        
        // Aplicar filtros baseado no role do usuário logado
        if ($loggedUser->role === 'admin') {
            // Admin: lista todos os usuários médicos
            // (sem filtro adicional)
        } elseif ($loggedUser->role === 'user') {
            // Usuário comum: lista apenas médicos que têm relação com ele
            $allowedDoctorIds = $loggedUser->allowedDoctors()->pluck('doctors.id')->toArray();
            // Incluir o médico atual se ele estiver relacionado
            if (in_array($doctor->id, $allowedDoctorIds)) {
                $allowedDoctorIds[] = $doctor->id;
            }
            $allowedDoctorIds = array_unique($allowedDoctorIds);
            
            if (!empty($allowedDoctorIds)) {
                // Buscar os user_id dos médicos relacionados
                $relatedUserIds = Doctor::whereIn('id', $allowedDoctorIds)
                    ->pluck('user_id')
                    ->toArray();
                // Garantir que o usuário atual do médico está incluído
                $relatedUserIds[] = $doctor->user_id;
                $relatedUserIds = array_unique($relatedUserIds);
                $query->whereIn('id', $relatedUserIds);
            } else {
                // Se não tem médicos permitidos, só mostra o usuário atual do médico
                $query->where('id', $doctor->user_id);
            }
        } elseif ($loggedUser->role === 'doctor' && $loggedUser->doctor) {
            // Usuário médico: lista apenas médicos que têm relação com ele
            $allowedDoctorIds = $loggedUser->allowedDoctors()->pluck('doctors.id')->toArray();
            // Incluir também o próprio médico e o médico atual
            $allowedDoctorIds[] = $loggedUser->doctor->id;
            if ($doctor->id !== $loggedUser->doctor->id) {
                $allowedDoctorIds[] = $doctor->id;
            }
            $allowedDoctorIds = array_unique($allowedDoctorIds);
            
            if (!empty($allowedDoctorIds)) {
                // Buscar os user_id dos médicos relacionados
                $relatedUserIds = Doctor::whereIn('id', $allowedDoctorIds)
                    ->pluck('user_id')
                    ->toArray();
                // Garantir que o usuário atual do médico está incluído
                $relatedUserIds[] = $doctor->user_id;
                $relatedUserIds = array_unique($relatedUserIds);
                $query->whereIn('id', $relatedUserIds);
            } else {
                // Se não tem médicos permitidos, só mostra o usuário atual do médico
                $query->where('id', $doctor->user_id);
            }
        }
        
        $users = $query->orderBy('name')->get();
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $doctor->load('specialties');

        return view('tenant.doctors.edit', compact('doctor', 'users', 'specialties'));
    }

    public function update(UpdateDoctorRequest $request, $slug, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $data = $request->validated();

        $doctor->update([
            'user_id'   => $data['user_id'],
            'crm_number' => $data['crm_number'] ?? null,
            'crm_state' => $data['crm_state'] ?? null,
            'signature' => $data['signature'] ?? null,
            'label_singular' => $data['label_singular'] ?? null,
            'label_plural' => $data['label_plural'] ?? null,
            'registration_label' => $data['registration_label'] ?? null,
            'registration_value' => $data['registration_value'] ?? null,
        ]);

        if (!empty($data['specialties'])) {
            $doctor->specialties()->sync($data['specialties']);
        } else {
            $doctor->specialties()->detach();
        }

        return redirect()->route('tenant.doctors.index', ['slug' => $slug])
            ->with('success', 'Médico atualizado com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $doctor = Doctor::findOrFail($id);

        // Verificar se o médico possui atendimentos
        if ($doctor->hasAppointments()) {
            return redirect()->route('tenant.doctors.index', ['slug' => $slug])
                ->with('error', 'Não é possível excluir o médico pois ele possui atendimentos cadastrados.');
        }

        $doctor->delete();

        return redirect()->route('tenant.doctors.index', ['slug' => $slug])
            ->with('success', 'Médico removido com sucesso.');
    }
}
