<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorDevice;

class VisitorDevicePolicy
{
    /**
     * Determine whether the user can view any devices.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can view the device.
     */
    public function view(User $user, VisitorDevice $device): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Device manager or users with admin roles can view
        return $device->managed_by === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can create devices.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Agency', 'HR']);
    }

    /**
     * Determine whether the user can update the device.
     */
    public function update(User $user, VisitorDevice $device): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Device manager or admin roles can update
        return $device->managed_by === $user->id ||
               $user->hasAnyRole(['Agency', 'HR']);
    }

    /**
     * Determine whether the user can delete the device.
     */
    public function delete(User $user, VisitorDevice $device): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Only device manager or super admin can delete
        return $device->managed_by === $user->id;
    }
}