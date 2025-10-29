<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
    public function before(User $user, $ability)
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }
    }

    public function view(User $user, Agency $agency): bool
    {
        return $user->tenant_id && $user->tenant_id == $agency->tenant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->tenant_id;
    }

    public function update(User $user, Agency $agency): bool
    {
        return $this->view($user, $agency);
    }

    public function delete(User $user, Agency $agency): bool
    {
        return $this->view($user, $agency);
    }
}
