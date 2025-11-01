<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_creates_attendance()
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'T', 'domain' => 't.test', 'uuid' => \Illuminate\Support\Str::uuid()->toString(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->tenant_uuid = DB::table('tenants')->where('id', $tenantId)->value('uuid');
        $this->actingAs($user);

        $resp = $this->postJson('/api/attendance/checkin', ['employee_id' => 1, 'tenant_uuid' => $user->tenant_uuid, 'check_in_type' => 'manual']);
        $resp->assertStatus(201);

        $this->assertDatabaseHas('attendances', ['employee_id' => 1, 'tenant_uuid' => $user->tenant_uuid]);
    }

    public function test_duplicate_checkin_returns_409()
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'T', 'domain' => 't.test', 'uuid' => \Illuminate\Support\Str::uuid()->toString(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->tenant_uuid = DB::table('tenants')->where('id', $tenantId)->value('uuid');
        $this->actingAs($user);

        $this->postJson('/api/attendance/checkin', ['employee_id' => 2, 'tenant_uuid' => $user->tenant_uuid, 'check_in_type' => 'manual']);
        $resp = $this->postJson('/api/attendance/checkin', ['employee_id' => 2, 'tenant_uuid' => $user->tenant_uuid, 'check_in_type' => 'manual']);
        $resp->assertStatus(409);
    }

    public function test_qr_checkin_validates_code()
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'T', 'domain' => 't.test', 'uuid' => \Illuminate\Support\Str::uuid()->toString(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->tenant_uuid = DB::table('tenants')->where('id', $tenantId)->value('uuid');
        $this->actingAs($user);

        $resp = $this->postJson('/api/attendance/checkin', [
            'employee_id' => 3,
            'tenant_uuid' => $user->tenant_uuid,
            'check_in_type' => 'qr',
            'check_in_meta' => ['qr_code' => 'ok123']
        ]);

        $resp->assertStatus(201);
    }
}
