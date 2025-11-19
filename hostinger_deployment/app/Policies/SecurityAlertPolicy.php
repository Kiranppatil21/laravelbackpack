<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SecurityAlert;

class SecurityAlertPolicy
{
    /**
     * Determine whether the user can view any security alerts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can view the alert.
     */
    public function view(User $user, SecurityAlert $alert): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Assigned user or users with security roles can view
        return $alert->assigned_to === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can create alerts.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can update the alert.
     */
    public function update(User $user, SecurityAlert $alert): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Assigned user or users with security roles can update
        return $alert->assigned_to === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can delete the alert.
     */
    public function delete(User $user, SecurityAlert $alert): bool
    {
        return $user->hasRole('Super Admin');
    }
}