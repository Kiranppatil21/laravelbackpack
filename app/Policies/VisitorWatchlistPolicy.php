<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorWatchlist;

class VisitorWatchlistPolicy
{
    /**
     * Determine whether the user can view any watchlist entries.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can view the watchlist entry.
     */
    public function view(User $user, VisitorWatchlist $watchlist): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can create watchlist entries.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can update the watchlist entry.
     */
    public function update(User $user, VisitorWatchlist $watchlist): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Only the user who added the entry or admin roles can update
        return $watchlist->added_by === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can delete the watchlist entry.
     */
    public function delete(User $user, VisitorWatchlist $watchlist): bool
    {
        return $this->update($user, $watchlist);
    }
}