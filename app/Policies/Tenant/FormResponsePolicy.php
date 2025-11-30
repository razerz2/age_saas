<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\FormResponse;

class FormResponsePolicy
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
    public function view(User $user, FormResponse $formResponse): bool
    {
        // Carrega o relacionamento se necessário
        if (!$formResponse->relationLoaded('form')) {
            $formResponse->load('form');
        }

        // Se o formulário não tem doctor_id, admin pode ver
        if (!$formResponse->form->doctor_id) {
            return $user->role === 'admin';
        }

        return $this->belongsToUser($user, $formResponse->form->doctor_id);
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
    public function update(User $user, FormResponse $formResponse): bool
    {
        return $this->view($user, $formResponse);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FormResponse $formResponse): bool
    {
        return $this->view($user, $formResponse);
    }
}
