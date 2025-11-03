<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceStatutoryCsvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_gst_adhoc_csv_download_headers_and_content()
    {
        // create two invoices with line items via the API to ensure data exists
        $payload1 = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service A', 'qty' => 2, 'unit_price' => 100, 'tax_rate' => 18],
            ],
        ];
        $this->postJson('/api/finance/invoices', $payload1)->assertStatus(201);

        $payload2 = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service B', 'qty' => 1, 'unit_price' => 200, 'tax_rate' => 12],
            ],
        ];
        $this->postJson('/api/finance/invoices', $payload2)->assertStatus(201);

        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        $resp = $this->post('/api/finance/reports/statutory/download', [
            'type' => 'gst',
            'period_start' => $start,
            'period_end' => $end,
        ]);

        // streaming download returns 200 with CSV content-type
        $resp->assertStatus(200);
    $this->assertStringContainsString('text/csv', $resp->headers->get('content-type'));
    $this->assertStringContainsString('attachment', strtolower($resp->headers->get('content-disposition') ?? ''));

        $content = $resp->getContent();
        // metadata header present
        $this->assertStringContainsString('type,period_start,period_end', $content);
        // GST columns should include tax_rate
    $this->assertStringContainsString('tax_rate', $content);
    // ensure one of the tax rates from created invoices exists in CSV
    $this->assertStringContainsString('18', $content);

        // Locate the first blank line (metadata separator) and treat the following non-empty line as the CSV header
        $allLines = array_map('rtrim', explode("\n", $content));
        $lines = array_values(array_map('trim', $allLines));
        $blankIdx = null;
        foreach ($lines as $i => $ln) {
            if ($ln === '') { $blankIdx = $i; break; }
        }
        $this->assertNotNull($blankIdx, 'No metadata separator blank line found in CSV');
        $headerLine = $lines[$blankIdx + 1] ?? null;
        $this->assertNotNull($headerLine, 'No header line after metadata separator');
        // header should contain alphabetic column names or at least one of the known GST column names
        $this->assertMatchesRegularExpression('/(tax_rate|taxable_value|tax_amount)/i', $headerLine);
    }
}
