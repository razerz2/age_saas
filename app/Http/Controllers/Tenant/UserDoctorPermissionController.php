<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\UserDoctorPermission;
use App\Services\Tenant\ProfessionalLabelService;
use Illuminate\Http\Request;

class UserDoctorPermissionController extends Controller
{
    public function __construct(
        private readonly ProfessionalLabelService $professionalLabelService
    ) {
    }

    /**
     * Exibe a página de gerenciamento de permissões de um usuário.
     */
    public function index($slug, $userId)
    {
        $user = User::findOrFail($userId);
        $professionalLabels = $this->professionalLabels();

        // Não permite gerenciar permissões para médicos e admins
        if ($user->role === 'doctor' || $user->role === 'admin') {
            return redirect()->route('tenant.users.index', ['slug' => tenant()->subdomain])
                ->with('error', ucfirst($professionalLabels['plural']) . ' e administradores não precisam de permissões. Eles têm acesso completo às suas próprias agendas.');
        }

        $doctors = Doctor::with('user')->orderBy('id')->get();
        $userPermissions = $user->doctorPermissions()->pluck('doctor_id')->toArray();

        return view('tenant.user-doctor-permissions.index', compact('user', 'doctors', 'userPermissions', 'professionalLabels'));
    }

    /**
     * Atualiza as permissões de um usuário.
     */
    public function update(Request $request, $slug, $userId)
    {
        $user = User::findOrFail($userId);
        $professionalLabels = $this->professionalLabels();

        // Não permite gerenciar permissões para médicos e admins
        if ($user->role === 'doctor' || $user->role === 'admin') {
            return redirect()->route('tenant.users.index', ['slug' => tenant()->subdomain])
                ->with('error', ucfirst($professionalLabels['plural']) . ' e administradores não precisam de permissões.');
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

        return redirect()->route('tenant.users.show', ['slug' => tenant()->subdomain, 'id' => $user->id])
            ->with('success', 'Permissões de ' . $professionalLabels['plural_lower'] . ' atualizadas com sucesso.');
    }

    /**
     * @return array{singular:string,plural:string,registration:string,singular_lower:string,plural_lower:string}
     */
    private function professionalLabels(): array
    {
        $singular = $this->professionalLabelService->singular();
        $plural = $this->professionalLabelService->plural();
        $registration = $this->professionalLabelService->registration();

        $toLower = static fn (string $value): string => function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        return [
            'singular' => $singular,
            'plural' => $plural,
            'registration' => $registration,
            'singular_lower' => $toLower($singular),
            'plural_lower' => $toLower($plural),
        ];
    }

    /**
     * API: Retorna os médicos que o usuário pode visualizar.
     */
    public function getAllowedDoctors($slug, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->role === 'doctor' && $user->doctor) {
            // Médico só pode ver a si mesmo
            return response()->json([$user->doctor->id]);
        }

        if ($user->role === 'admin') {
            // Admin pode ver todos os médicos
            $doctors = Doctor::pluck('id')->toArray();
            return response()->json($doctors);
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
