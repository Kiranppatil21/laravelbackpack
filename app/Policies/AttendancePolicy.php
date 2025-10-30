<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    public function view(User $user, Attendance $attendance)
    {
        // Simple check: allow if user's tenant_uuid matches attendance tenant_uuid
        return isset($user->tenant_uuid) && $user->tenant_uuid === $attendance->tenant_uuid;
    }

    public function create(User $user)
    {
        // Allow authenticated users to create attendance for now
        return $user !== null;
    }
}
