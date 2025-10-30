<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    public function before(User $user, $ability)
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->tenant_id && $employee->tenant_id == $user->tenant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->tenant_id;
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->tenant_id && $employee->tenant_id == $user->tenant_id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->tenant_id && $employee->tenant_id == $user->tenant_id;
    }
}
