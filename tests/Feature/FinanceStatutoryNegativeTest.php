<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceStatutoryNegativeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_invalid_type_returns_validation_error()
    {
        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        $resp = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'invalid_type',
            'period_start' => $start,
            'period_end' => $end,
        ]);

        $resp->assertStatus(422);
        $body = $resp->json();
        $this->assertArrayHasKey('errors', $body);
    }

    public function test_missing_dates_returns_validation_error()
    {
        $resp = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'gst'
        ]);

        $resp->assertStatus(422);
        $body = $resp->json();
        $this->assertArrayHasKey('errors', $body);
    }

    public function test_no_data_period_returns_note_in_csv()
    {
        $start = '1999-01-01';
        $end = '1999-01-02';

        $resp = $this->post('/api/finance/reports/statutory/download', [
            'type' => 'gst',
            'period_start' => $start,
            'period_end' => $end,
        ]);

    $resp->assertStatus(200);
    $this->assertStringContainsString('text/csv', $resp->headers->get('content-type'));
        $content = $resp->getContent();
        $this->assertStringContainsString('no rows computed', $content);
    }
}
