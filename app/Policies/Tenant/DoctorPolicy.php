<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Doctor;

class DoctorPolicy
{
    use HandlesDoctorPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin pode ver todos
        if ($user->role === 'admin') {
            return true;
        }

        // Usuário comum e médico podem ver (com filtros aplicados no controller)
        return in_array($user->role, ['user', 'doctor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Doctor $doctor): bool
    {
        return $this->belongsToUser($user, $doctor->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin e usuário comum podem criar médicos
        return in_array($user->role, ['admin', 'user']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Doctor $doctor): bool
    {
        return $this->belongsToUser($user, $doctor->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Doctor $doctor): bool
    {
        // Apenas admin pode deletar
        return $user->role === 'admin';
    }
}
