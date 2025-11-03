<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Invoice;

class FinanceReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // create and authenticate a user for protected API routes
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_generate_gst_statutory_report()
    {
        // create an invoice with a taxed line item
        $payload = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Svc', 'qty' => 1, 'unit_price' => 100, 'tax_rate' => 18],
            ],
        ];

        $resp = $this->postJson('/api/finance/invoices', $payload);
        $resp->assertStatus(201);

        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        $reportResp = $this->postJson('/api/finance/reports/statutory', [
            'type' => 'gst',
            'period_start' => $start,
            'period_end' => $end,
        ]);

        $reportResp->assertStatus(201);
        $body = $reportResp->json();
        $this->assertArrayHasKey('payload', $body);
        $this->assertArrayHasKey('summary', $body['payload']);
    }

    public function test_profitability_summary()
    {
        $payload = [
            'issued_date' => now()->toDateString(),
            'items' => [ ['description' => 'A','qty'=>1,'unit_price'=>200,'tax_rate'=>0] ],
        ];
        $this->postJson('/api/finance/invoices', $payload)->assertStatus(201);

        $resp = $this->getJson('/api/finance/reports/profitability?period_start='.now()->subDay()->toDateString().'&period_end='.now()->addDay()->toDateString());
        $resp->assertStatus(200);
        $this->assertArrayHasKey('revenue', $resp->json());
    }
}
