<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Http\Requests\Tenant\ChangePasswordUserRequest;
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

        // Garante que modules seja um array (vazio se não informado)
        $data['modules'] = $data['modules'] ?? [];

        // Upload do avatar se fornecido
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = 'avatars/' . time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public', $avatarName);
            $data['avatar'] = $avatarName;
        }

        User::create($data);

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

        // Se o campo 'modules' for enviado, tratamos os módulos
        if (isset($data['modules'])) {
            $data['modules'] = json_encode($data['modules']);
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

        // Atualiza os dados do usuário (sem a senha)
        $user->update($data);

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
