<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de perfil do usuário.
     */
    public function edit()
    {
        $user = Auth::guard('tenant')->user();
        return view('tenant.profile.edit', compact('user'));
    }

    /**
     * Atualiza as informações do perfil do usuário.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::guard('tenant')->user();
        $data = $request->validated();

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

        // Se a senha foi informada, atualiza
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Remove password_confirmation dos dados
        unset($data['password_confirmation']);

        $user->update($data);

        return redirect()->route('tenant.profile.edit', ['slug' => tenant()->subdomain])
            ->with('success', 'Perfil atualizado com sucesso!');
    }
}

