<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\NetworkUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class NetworkUserController extends Controller
{
    /**
     * Adiciona um novo usuário à rede
     */
    public function store(Request $request, ClinicNetwork $network)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:network_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'finance', 'manager'])],
            'is_active' => ['boolean'],
        ]);

        $validated['clinic_network_id'] = $network->id;
        $validated['is_active'] = $request->has('is_active');

        NetworkUser::create($validated);

        return back()->with('success', 'Usuário de rede criado com sucesso!');
    }

    /**
     * Remove um usuário da rede
     */
    public function destroy(ClinicNetwork $network, NetworkUser $user)
    {
        abort_if($user->clinic_network_id !== $network->id, 403, 'Este usuário não pertence a esta rede.');

        $user->delete();

        return back()->with('success', 'Usuário de rede removido com sucesso!');
    }

    /**
     * Atualiza o status do usuário
     */
    public function toggleStatus(ClinicNetwork $network, NetworkUser $user)
    {
        abort_if($user->clinic_network_id !== $network->id, 403, 'Este usuário não pertence a esta rede.');

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'Status do usuário atualizado!');
    }
}
