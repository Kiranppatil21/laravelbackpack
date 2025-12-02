<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function before(User $user, $ability)
    {
        // Allow Super Admin users full access regardless of tenant
        if ($user && $user->hasRole('Super Admin')) {
            return true;
        }
        return null;
    }

    public function view(User $user, Client $client): bool
    {
        // Super Admin can view all clients
        if ($user && $user->hasRole('Super Admin')) {
            return true;
        }
        
        // Other users can only view clients in their tenant
        return $user && $user->tenant_id && $user->tenant_id == $client->tenant_id;
    }

    public function create(User $user): bool
    {
        // Super Admin can create clients anywhere
        if ($user && $user->hasRole('Super Admin')) {
            return true;
        }
        
        // Other users need to be in a tenant
        return $user && (bool) $user->tenant_id;
    }

    public function update(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }
}
