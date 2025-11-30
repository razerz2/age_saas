<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;

trait HandlesDoctorPermissions
{
    /**
     * Verifica se o usuário tem acesso a um médico específico
     */
    protected function belongsToUser(User $user, $doctorId): bool
    {
        // Admin tem acesso a todos os médicos
        if ($user->role === 'admin') {
            return true;
        }

        // Se for role doctor, só pode acessar seu próprio médico
        if ($user->role === 'doctor') {
            return $user->doctor && (string) $user->doctor->id === (string) $doctorId;
        }

        // Usuário comum: verifica permissões
        return $user->allowedDoctors()->where('doctors.id', $doctorId)->exists();
    }
}

