<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorInvitation;

class VisitorInvitationPolicy
{
    /**
     * Determine whether the user can view any invitations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can view the invitation.
     */
    public function view(User $user, VisitorInvitation $invitation): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Host or inviter can view
        return $invitation->host_id === $user->id || 
               $invitation->invited_by === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can create invitations.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can update the invitation.
     */
    public function update(User $user, VisitorInvitation $invitation): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Only inviter can update
        return $invitation->invited_by === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can delete the invitation.
     */
    public function delete(User $user, VisitorInvitation $invitation): bool
    {
        return $this->update($user, $invitation);
    }
}