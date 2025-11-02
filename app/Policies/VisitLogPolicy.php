<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitLog;

class VisitLogPolicy
{
    /**
     * Determine whether the user can view any visit logs.
     * Allow Super Admin or Agency roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Agency');
    }

    /**
     * Determine whether the user can view a specific visit log.
     * Allow the host (owner) or those roles.
     */
    public function view(User $user, VisitLog $visit): bool
    {
        if ($user->hasRole('Super Admin') || $user->hasRole('Agency')) {
            return true;
        }

        // allow host user if host_id matches user's id (if hosts are users)
        return $visit->host_id && $user->id === (int) $visit->host_id;
    }

    /**
     * Allow update (checkout) for Super Admin/Agency or host.
     */
    public function update(User $user, VisitLog $visit): bool
    {
        return $this->view($user, $visit);
    }
}
