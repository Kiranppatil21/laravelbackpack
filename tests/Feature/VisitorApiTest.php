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

        // Prepare auth headers depending on environment variables.
        // If VISITOR_API_KEY is set, include it. If HMAC secret is set, sign the payload.
        $headers = [];
        if (env('VISITOR_API_KEY')) {
            $headers['X-VISITOR-API-KEY'] = env('VISITOR_API_KEY');
        }

        // If HMAC signing is required in the environment, compute signature over the raw JSON payload
        // so tests pass both in CI (where a secret may be configured) and locally.
        if (! empty(env('VISITOR_HMAC_SECRET'))) {
            $content = json_encode($payload);
            $timestamp = (string) time();
            $message = $timestamp . '|' . $content;
            $sig = hash_hmac('sha256', $message, env('VISITOR_HMAC_SECRET'));
            $headers['X-VISITOR-SIGNATURE'] = $sig;
            $headers['X-VISITOR-TIMESTAMP'] = $timestamp;
        }

        $response = $this->postJson('/api/visitors/checkin', $payload, $headers);
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

