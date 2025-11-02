<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Services\VisitorSignature;
use App\Models\User;

class VisitorCISmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_checkin_queues_notification_job_or_api_key_used()
    {
        // This smoke test relies on CI secrets. Skip locally if not present.
        if (empty(env('VISITOR_API_KEY')) && empty(env('VISITOR_HMAC_SECRET'))) {
            $this->markTestSkipped('No VISITOR_API_KEY or VISITOR_HMAC_SECRET configured');
        }

        $this->seed(\Database\Seeders\TestRolesSeeder::class);

        // Intercept queue pushes so we can assert the notification job was queued
        Queue::fake();

        $host = User::factory()->create();

        $payload = [
            'name' => 'CI Visitor',
            'email' => 'ci@example.test',
            'phone' => '9000000001',
            'host_id' => $host->id,
            'source' => 'ci-smoke',
        ];

        $headers = [];
        if (! empty(env('VISITOR_API_KEY'))) {
            $headers['X-VISITOR-API-KEY'] = env('VISITOR_API_KEY');
        } else {
            $payloadStr = json_encode($payload);
            $s = VisitorSignature::sign($payloadStr, env('VISITOR_HMAC_SECRET'));
            $headers['X-VISITOR-TIMESTAMP'] = $s['timestamp'];
            $headers['X-VISITOR-SIGNATURE'] = $s['signature'];
        }

        $response = $this->postJson('/api/visitors/checkin', $payload, $headers);
        $response->assertStatus(201);

        // Assert that a queued notification job was pushed (SendQueuedNotifications)
        Queue::assertPushed(\Illuminate\Notifications\SendQueuedNotifications::class);
    }
}
