<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\UserDoctorPermission;
use Illuminate\Http\Request;

class UserDoctorPermissionController extends Controller
{
    /**
     * Exibe a página de gerenciamento de permissões de um usuário
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        
        // Não permite gerenciar permissões para médicos
        if ($user->is_doctor) {
            return redirect()->route('tenant.users.index')
                ->with('error', 'Médicos não precisam de permissões. Eles só visualizam suas próprias agendas.');
        }
        
        $doctors = Doctor::with('user')->orderBy('id')->get();
        $userPermissions = $user->doctorPermissions()->pluck('doctor_id')->toArray();
        
        return view('tenant.user-doctor-permissions.index', compact('user', 'doctors', 'userPermissions'));
    }

    /**
     * Atualiza as permissões de um usuário
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // Não permite gerenciar permissões para médicos
        if ($user->is_doctor) {
            return redirect()->route('tenant.users.index')
                ->with('error', 'Médicos não precisam de permissões.');
        }
        
        $request->validate([
            'doctor_ids' => 'nullable|array',
            'doctor_ids.*' => 'exists:tenant.doctors,id',
        ]);
        
        // Remove todas as permissões existentes
        $user->doctorPermissions()->delete();
        
        // Adiciona as novas permissões
        if ($request->has('doctor_ids') && is_array($request->doctor_ids)) {
            foreach ($request->doctor_ids as $doctorId) {
                UserDoctorPermission::create([
                    'user_id' => $user->id,
                    'doctor_id' => $doctorId,
                ]);
            }
        }
        
        return redirect()->route('tenant.users.show', $user->id)
            ->with('success', 'Permissões de médicos atualizadas com sucesso.');
    }

    /**
     * API: Retorna os médicos que o usuário pode visualizar
     */
    public function getAllowedDoctors($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->is_doctor && $user->doctor) {
            // Médico só pode ver a si mesmo
            return response()->json([$user->doctor->id]);
        }
        
        if ($user->canViewAllDoctors()) {
            // Pode ver todos os médicos
            $doctors = Doctor::pluck('id')->toArray();
            return response()->json($doctors);
        }
        
        // Retorna apenas os médicos permitidos
        $allowedDoctorIds = $user->doctorPermissions()->pluck('doctor_id')->toArray();
        return response()->json($allowedDoctorIds);
    }
}

