<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\VisitLog;

class AdminVisitorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_can_fetch_paginated_visitor_logs()
    {
        $this->seed(\Database\Seeders\TestRolesSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Agency');

        VisitLog::factory()->count(3)->create(['host_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/admin/api/visitors/logs');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'current_page', 'per_page']);
    }

    public function test_guest_cannot_access_admin_visitor_api()
    {
        $response = $this->getJson('/admin/api/visitors/logs');
        // API endpoints return 401 for unauthenticated JSON requests
        $response->assertStatus(401);
    }
}
