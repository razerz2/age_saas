<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\AppointmentType;

class AppointmentTypePolicy
{
    use HandlesDoctorPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtros aplicados no controller
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AppointmentType $appointmentType): bool
    {
        return $this->belongsToUser($user, $appointmentType->doctor_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin e usuário comum podem criar
        return in_array($user->role, ['admin', 'user']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AppointmentType $appointmentType): bool
    {
        return $this->view($user, $appointmentType);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AppointmentType $appointmentType): bool
    {
        return $this->view($user, $appointmentType);
    }
}
