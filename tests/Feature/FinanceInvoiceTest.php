<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Invoice;

class FinanceInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_invoice_and_compute_totals()
    {
        $payload = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service A', 'qty' => 2, 'unit_price' => 100, 'tax_rate' => 18],
                ['description' => 'Service B', 'qty' => 1, 'unit_price' => 200, 'tax_rate' => 18],
            ],
        ];

        $resp = $this->postJson('/api/finance/invoices', $payload);
        $resp->assertStatus(201);

        $body = $resp->json();
        $this->assertArrayHasKey('id', $body);
        $this->assertEquals( (2*100) + (1*200) + ((200*0.18)+(200*0.18)), $body['total_amount']);
    }

    public function test_record_payment_and_update_status()
    {
        $payload = [
            'issued_date' => now()->toDateString(),
            'items' => [
                ['description' => 'Service A', 'qty' => 1, 'unit_price' => 100, 'tax_rate' => 0],
            ],
        ];

        $resp = $this->postJson('/api/finance/invoices', $payload);
        $resp->assertStatus(201);
        $invoice = Invoice::first();

        $pay = $this->postJson('/api/finance/invoices/'.$invoice->id.'/payments', ['amount' => 100]);
        $pay->assertStatus(201);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }
}
