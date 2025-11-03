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
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

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
    $invoice = Invoice::find($body['id']);
    // debug dump to STDERR to inspect DB row during test runs
    fwrite(STDERR, "DB invoice row: ".print_r(\DB::table('invoices')->where('id', $body['id'])->first(), true)."\n");
    $expected = (2*100) + (1*200);
    $expected += (2*100)*(18/100) + (1*200)*(18/100);
    // invoice uses `total` column in this project
    $this->assertEquals((float) number_format($expected, 2, '.', ''), (float) $invoice->total);
    }

    public function test_record_payment_and_update_status()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');

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
