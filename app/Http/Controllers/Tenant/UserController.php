<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Http\Requests\Tenant\ChangePasswordUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('tenant.users.index', compact('users'));
    }

    public function create()
    {
        return view('tenant.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        
        // Se a senha não foi informada, gera uma senha aleatória
        if (empty($data['password'])) {
            $data['password'] = Str::random(12);
        }
        
        $data['password'] = Hash::make($data['password']);
        
        // Remove password_confirmation dos dados antes de salvar
        unset($data['password_confirmation']);

        // Verifica o role do usuário logado
        $loggedUser = Auth::guard('tenant')->user();

        // Se o usuário logado é admin, não permite alterar modules (admin tem acesso a tudo)
        if ($loggedUser && $loggedUser->role === 'admin') {
            unset($data['modules']);
        } else {
            // Garante que modules seja um array (vazio se não informado)
            $data['modules'] = $data['modules'] ?? [];
        }

        // Garante que role tenha um valor padrão
        $data['role'] = $data['role'] ?? 'user';

        // Upload do avatar se fornecido
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = 'avatars/' . time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public', $avatarName);
            $data['avatar'] = $avatarName;
        }

        // Extrair doctor_ids e doctor_id antes de criar o usuário
        // Se o usuário logado é médico ou admin, ignora doctor_ids
        $doctorIds = [];
        if ($loggedUser && $loggedUser->role !== 'doctor' && $loggedUser->role !== 'admin') {
            $doctorIds = $request->input('doctor_ids', []);
        }
        $doctorId = $request->input('doctor_id');
        unset($data['doctor_ids'], $data['doctor_id']);

        $user = User::create($data);

        // Se for role 'user', salvar permissões de médicos
        if ($user->role === 'user' && !empty($doctorIds)) {
            foreach ($doctorIds as $docId) {
                \App\Models\Tenant\UserDoctorPermission::create([
                    'user_id' => $user->id,
                    'doctor_id' => $docId,
                ]);
            }
        }

        // Se for role 'doctor', vincular ao médico selecionado
        if ($user->role === 'doctor' && $doctorId) {
            // O médico já deve estar vinculado ao usuário através do campo user_id na tabela doctors
            // Mas podemos verificar se precisa criar o vínculo
            $doctor = \App\Models\Tenant\Doctor::find($doctorId);
            if ($doctor && $doctor->user_id !== $user->id) {
                $doctor->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Mostra as informações do usuário.
     * Agora usando o ID explícito.
     */
    public function show($id)
    {
        $user = User::with(['allowedDoctors.user'])->findOrFail($id);  // Utilizando o ID passado na rota

        return view('tenant.users.show', compact('user'));
    }

    /**
     * Exibe o formulário de edição do usuário.
     * Agora usando o ID explícito.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);  // Utilizando o ID passado na rota

        return view('tenant.users.edit', compact('user'));
    }

    /**
     * Atualiza os dados do usuário.
     * Agora usando o ID explícito.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // Valida os dados da requisição
        $data = $request->validated();

        // Verifica o role do usuário logado
        $loggedUser = Auth::guard('tenant')->user();

        // Se o usuário logado é admin, não permite alterar modules (admin tem acesso a tudo)
        if ($loggedUser && $loggedUser->role === 'admin') {
            unset($data['modules']);
        } elseif (isset($data['modules'])) {
            // Se o campo 'modules' for enviado, tratamos os módulos
            $data['modules'] = json_encode($data['modules']);
        }

        // Garante que role tenha um valor padrão
        if (!isset($data['role'])) {
            $data['role'] = $user->role ?? 'user';
        }

        // Upload do novo avatar se fornecido
        if ($request->hasFile('avatar')) {
            // Remove avatar antigo se existir
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatar = $request->file('avatar');
            $avatarName = 'avatars/' . time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public', $avatarName);
            $data['avatar'] = $avatarName;
        }

        // Extrair doctor_ids e doctor_id antes de atualizar o usuário
        // Se o usuário logado é médico ou admin, ignora doctor_ids
        $doctorIds = [];
        if ($loggedUser && $loggedUser->role !== 'doctor' && $loggedUser->role !== 'admin') {
            $doctorIds = $request->input('doctor_ids', []);
        }
        $doctorId = $request->input('doctor_id');
        unset($data['doctor_ids'], $data['doctor_id']);

        // Atualiza os dados do usuário (sem a senha)
        $user->update($data);

        // Atualizar permissões de médicos se for role 'user'
        if ($user->role === 'user') {
            // Remove todas as permissões existentes
            $user->doctorPermissions()->delete();
            // Adiciona as novas permissões
            if (!empty($doctorIds)) {
                foreach ($doctorIds as $docId) {
                    \App\Models\Tenant\UserDoctorPermission::create([
                        'user_id' => $user->id,
                        'doctor_id' => $docId,
                    ]);
                }
            }
        }

        // Se for role 'doctor', vincular ao médico selecionado
        if ($user->role === 'doctor' && $doctorId) {
            $doctor = \App\Models\Tenant\Doctor::find($doctorId);
            if ($doctor) {
                $doctor->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }


    /**
     * Remove o usuário.
     * Agora usando o ID explícito.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);  // Utilizando o ID passado na rota

        $user->delete();

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuário removido.');
    }

    public function showChangePasswordForm($id)
    {
        // Recupera o usuário pelo ID
        $user = User::findOrFail($id);

        // Retorna a view, passando o usuário
        return view('tenant.users.change-password', compact('user'));
    }

    public function changePassword(ChangePasswordUserRequest $request, $id)
    {
        // Valida os dados com a ChangePasswordRequest
        $validated = $request->validated();

        // Recupera o usuário pelo ID
        $user = User::findOrFail($id);

        // Verifica se a senha atual está correta
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        // Atualiza a senha
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Redireciona com sucesso
        return redirect()->route('tenant.users.index')
            ->with('success', 'Senha alterada com sucesso!');
    }
}
