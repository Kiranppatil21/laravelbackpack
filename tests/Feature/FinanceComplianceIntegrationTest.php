<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceComplianceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->assignRole('Agency Owner');
        $this->actingAs($this->user, 'sanctum');
        
        $this->client = Client::factory()->create(['tenant_id' => $this->user->id]);
    }

    public function test_complete_invoice_lifecycle()
    {
        // Create invoice
        $response = $this->postJson('/api/finance/invoices', [
            'client_id' => $this->client->id,
            'issued_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency' => 'INR',
            'items' => [
                [
                    'description' => 'Security Guard Services',
                    'qty' => 30,
                    'unit_price' => 1000,
                    'tax_rate' => 18
                ],
                [
                    'description' => 'Night Shift Premium',
                    'qty' => 10, 
                    'unit_price' => 500,
                    'tax_rate' => 18
                ]
            ]
        ]);

        $response->assertStatus(201);
        $invoice = $response->json();
        
        // Verify total calculation
        $expectedSubtotal = (30 * 1000) + (10 * 500); // 35000
        $expectedTax = $expectedSubtotal * 0.18; // 6300
        $expectedTotal = $expectedSubtotal + $expectedTax; // 41300
        
        $this->assertEquals($expectedTotal, $invoice['total']);
        $this->assertEquals('draft', $invoice['status']);

        // Record partial payment
        $response = $this->postJson("/api/finance/invoices/{$invoice['id']}/payments", [
            'amount' => 20000,
            'paid_at' => now()->toISOString(),
            'method' => 'bank_transfer',
            'reference' => 'TXN123456'
        ]);

        $response->assertStatus(201);
        
        // Check invoice status updated to partial
        $invoice = Invoice::find($invoice['id']);
        $this->assertEquals('partial', $invoice->status);

        // Record remaining payment
        $response = $this->postJson("/api/finance/invoices/{$invoice->id}/payments", [
            'amount' => 21300,
            'method' => 'upi',
            'reference' => 'UPI789012'
        ]);

        $response->assertStatus(201);
        
        // Check invoice status updated to paid
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_gst_statutory_report_generation()
    {
        // Create invoices with different tax rates
        $this->postJson('/api/finance/invoices', [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service A', 'qty' => 1, 'unit_price' => 1000, 'tax_rate' => 18],
                ['description' => 'Service B', 'qty' => 2, 'unit_price' => 500, 'tax_rate' => 12]
            ]
        ]);

        $this->postJson('/api/finance/invoices', [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service C', 'qty' => 1, 'unit_price' => 2000, 'tax_rate' => 18],
            ]
        ]);

        // Generate GST report
        $response = $this->postJson('/api/finance/reports/statutory', [
            'type' => 'gst',
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(201);
        $report = $response->json();
        
        $this->assertArrayHasKey('payload', $report);
        $this->assertArrayHasKey('summary', $report['payload']);
        
        $summary = $report['payload']['summary'];
        $this->assertArrayHasKey('rows', $summary);
        
        // Should have rows for 18% and 12% tax rates
        $taxRates = collect($summary['rows'])->pluck('tax_rate')->toArray();
        $this->assertContains(18, $taxRates);
        $this->assertContains(12, $taxRates);
        
        // Verify tax calculations
        $rate18Row = collect($summary['rows'])->firstWhere('tax_rate', 18);
        $expectedTaxable18 = 1000 + 2000; // 3000
        $expectedTax18 = $expectedTaxable18 * 0.18; // 540
        
        $this->assertEquals($expectedTaxable18, $rate18Row['taxable_value']);
        $this->assertEquals($expectedTax18, $rate18Row['tax_amount']);
    }

    public function test_profitability_report()
    {
        // Create revenue through invoices
        $this->postJson('/api/finance/invoices', [
            'client_id' => $this->client->id,
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Monthly Security Service', 'qty' => 1, 'unit_price' => 100000, 'tax_rate' => 18]
            ]
        ]);

        // Create payroll data (costs)
        \DB::table('payrolls')->insert([
            'employee_id' => 1,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(), 
            'gross' => 50000,
            'tax' => 5000,
            'net' => 45000,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/api/finance/reports/profitability?' . http_build_query([
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]));

        $response->assertStatus(200);
        $report = $response->json();
        
        $this->assertArrayHasKey('revenue', $report);
        $this->assertArrayHasKey('costs', $report);
        $this->assertArrayHasKey('gross_margin', $report);
        $this->assertArrayHasKey('margin_percent', $report);
        
        $this->assertEquals(100000, $report['revenue']);
        $this->assertEquals(50000, $report['costs']);
        $this->assertEquals(50000, $report['gross_margin']);
        $this->assertEquals(50.0, $report['margin_percent']);
    }

    public function test_statutory_csv_download()
    {
        // Create invoice data for CSV export
        $this->postJson('/api/finance/invoices', [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Test Service', 'qty' => 1, 'unit_price' => 1000, 'tax_rate' => 18]
            ]
        ]);

        $response = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'gst',
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        
        $content = $response->getContent();
        $this->assertStringContainsString('tax_rate', $content);
        $this->assertStringContainsString('taxable_value', $content);
        $this->assertStringContainsString('tax_amount', $content);
        $this->assertStringContainsString('18', $content); // Tax rate
        $this->assertStringContainsString('1000', $content); // Taxable value
        $this->assertStringContainsString('180', $content); // Tax amount
    }

    public function test_tds_pf_esic_reports()
    {
        // Create payroll data for TDS/PF/ESIC calculations
        \DB::table('payrolls')->insert([
            [
                'employee_id' => 1,
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'gross' => 60000,
                'tax' => 6000,
                'net' => 54000,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 2, 
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'gross' => 40000,
                'tax' => 2000,
                'net' => 38000,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Test TDS report
        $response = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'tds',
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('payroll_tds', $content);
        $this->assertStringContainsString('8000', $content); // Total TDS (6000 + 2000)

        // Test PF report
        $response = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'pf',
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('estimated_pf', $content);
        // Estimated PF = total_gross * 0.4 * 0.12 = 100000 * 0.4 * 0.12 = 4800
        $this->assertStringContainsString('4800', $content);

        // Test ESIC report
        $response = $this->postJson('/api/finance/reports/statutory/download', [
            'type' => 'esic',
            'period_start' => now()->subDay()->toDateString(),
            'period_end' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('estimated_esic', $content);
        // Estimated ESIC = total_gross * 0.0475 = 100000 * 0.0475 = 4750
        $this->assertStringContainsString('4750', $content);
    }

    public function test_finance_data_tenant_isolation()
    {
        // Create invoice for current user
        $response = $this->postJson('/api/finance/invoices', [
            'client_id' => $this->client->id,
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service', 'qty' => 1, 'unit_price' => 1000, 'tax_rate' => 18]
            ]
        ]);
        $invoice = $response->json();

        // Create another user with different tenant
        $otherUser = User::factory()->create();
        $otherUser->assignRole('Agency Owner');

        // Other user should not see this invoice
        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson('/api/finance/invoices');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));

        // Other user should not access specific invoice
        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/finance/invoices/{$invoice['id']}");

        $response->assertStatus(403);
    }
}