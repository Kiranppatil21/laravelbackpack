<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
    }

    public function view(User $user, Client $client): bool
    {
        return $user->tenant_id && $user->tenant_id == $client->tenant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->tenant_id;
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
