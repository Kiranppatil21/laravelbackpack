<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VisitorCheckedIn;

class VisitorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_creates_visitor_and_visitlog(): void
    {
        $this->seed(\Database\Seeders\TestRolesSeeder::class);

        $payload = [
            'name' => 'Alice Visitor',
            'email' => 'alice@example.test',
            'phone' => '1234567890',
            'host_id' => 1,
            'source' => 'test',
        ];

        Notification::fake();

        $host = User::factory()->create();
        $payload['host_id'] = $host->id;

        $response = $this->postJson('/api/visitors/checkin', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('visitors', ['email' => 'alice@example.test', 'name' => 'Alice Visitor']);
        $this->assertDatabaseHas('visit_logs', ['source' => 'test']);

        Notification::assertSentTo($host, VisitorCheckedIn::class);
    }

    public function test_checkout_sets_check_out_on_visitlog(): void
    {
        $this->seed(\Database\Seeders\TestRolesSeeder::class);
        $visitor = Visitor::create(['name' => 'Bob', 'email' => 'bob@example.test']);
        $log = VisitLog::create(['visitor_id' => $visitor->id, 'check_in_at' => now()]);

    $user = User::factory()->create();
    $this->actingAs($user);
    $user->assignRole('Agency');

    $response = $this->postJson('/api/visitors/'.$log->id.'/checkout');
        $response->assertStatus(200);

        $this->assertNotNull($log->fresh()->check_out_at);
    }

    public function test_index_requires_role(): void
    {
        $this->seed(\Database\Seeders\TestRolesSeeder::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->getJson('/api/visitors/logs')->assertStatus(403);

        // Give role and try again
        $user->assignRole('Agency');
        $this->getJson('/api/visitors/logs')->assertStatus(200);
    }
}

