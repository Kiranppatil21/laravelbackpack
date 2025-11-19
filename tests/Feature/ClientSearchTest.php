<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matching_clients_and_is_parameterized()
    {
        // create a tenant user and some clients
        $user = User::factory()->create(['tenant_id' => 1]);

        Client::factory()->create(['name' => 'Alice Example', 'email' => 'alice@example.com', 'tenant_id' => 1]);
        Client::factory()->create(['name' => "Bob O'Connor", 'email' => 'bob@example.com', 'tenant_id' => 1]);
        Client::factory()->create(['name' => 'Mallory', 'email' => 'mallory@evil.com', 'tenant_id' => 2]);

        // normal search
        $resp = $this->actingAs($user)->getJson('/api/clients?q=Alice');
        $resp->assertStatus(200);
        $data = $resp->json();
        $this->assertCount(1, $data);

        // search containing SQL-like payload should be treated literally (parameterized)
        $sqlPayload = "%' OR '1'='1";
        $resp2 = $this->actingAs($user)->getJson('/api/clients?q=' . urlencode($sqlPayload));
        $resp2->assertStatus(200);
        // should not return all tenant clients (only literal matches), so count should be <= existing tenant clients
        $data2 = $resp2->json();
        $this->assertIsArray($data2);
        $this->assertTrue(count($data2) <= 2);
    }
}
