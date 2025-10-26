<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('platform.users.index', compact('users'));
    }

    public function create()
    {
        return view('platform.users.create');
    }

    public function store(UserRequest $request)
    {
        $data = $request->validate();
        $data['modules'] = $data['modules'] ?? [];
        User::create($data);

        return redirect()->route('Platform.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function show(User $user)
    {
        return view('platform.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('platform.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        // Já validado automaticamente pela FormRequest
        $data = $request->validated();

        // Garante que sempre seja array (mesmo se nada vier marcado)
        $data['modules'] = $data['modules'] ?? [];

        // Se a senha veio vazia, não altera
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('Platform.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('Platform.users.index')->with('success', 'Usuário excluído com sucesso.');
    }

    public function resetPassword(User $user)
    {
        // 🚫 Impede reset da própria senha pelo painel admin
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Você não pode redefinir sua própria senha por aqui. Use o menu de perfil.');
        }

        $newPassword = 'user' . rand(1000, 9999);
        $user->update(['password' => $newPassword]);

        return back()->with('success', "Senha redefinida para o usuário {$user->name}. Nova senha: {$newPassword}");
    }

    public function toggleStatus(User $user)
    {
        // 🚫 Impede bloquear a própria conta
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Você não pode bloquear sua própria conta.');
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        $msg = $user->status === 'active'
            ? 'Usuário reativado com sucesso.'
            : 'Usuário bloqueado com sucesso.';

        return back()->with('success', $msg);
    }
}
