<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class FinanceStatutoryTdsPfEsicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_tds_adhoc_csv_includes_payroll_tds()
    {
        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        // insert payroll rows with tax amounts
        DB::table('payrolls')->insert([
            ['employee_id' => 1, 'period_start' => $start, 'period_end' => $end, 'gross' => 50000, 'tax' => 5000, 'net' => 45000, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'period_start' => $start, 'period_end' => $end, 'gross' => 30000, 'tax' => 3000, 'net' => 27000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $resp = $this->post('/api/finance/reports/statutory/download', [
            'type' => 'tds',
            'period_start' => $start,
            'period_end' => $end,
        ]);

    $resp->assertStatus(200);
    $this->assertStringContainsString('text/csv', $resp->headers->get('content-type'));
    $this->assertStringContainsString('attachment', strtolower($resp->headers->get('content-disposition') ?? ''));

        $content = $resp->getContent();
        // Should contain payroll_tds kind and the summed tax 8000
        $this->assertStringContainsString('payroll_tds', $content);
        $this->assertStringContainsString('8000', $content);
    }

    public function test_pf_adhoc_csv_estimate_based_on_gross()
    {
        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        // create payrolls with gross total 100000 (two rows 50000 each)
        DB::table('payrolls')->insert([
            ['employee_id' => 1, 'period_start' => $start, 'period_end' => $end, 'gross' => 50000, 'tax' => 5000, 'net' => 45000, 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'period_start' => $start, 'period_end' => $end, 'gross' => 50000, 'tax' => 5000, 'net' => 45000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $resp = $this->post('/api/finance/reports/statutory/download', [
            'type' => 'pf',
            'period_start' => $start,
            'period_end' => $end,
        ]);

    $resp->assertStatus(200);
    $this->assertStringContainsString('text/csv', $resp->headers->get('content-type'));
    $this->assertStringContainsString('attachment', strtolower($resp->headers->get('content-disposition') ?? ''));

        $content = $resp->getContent();
        // estimated_pf = gross_total * 0.4 * 0.12 = 100000 * 0.4 * 0.12 = 4800
        $this->assertStringContainsString('estimated_pf', $content);
        $this->assertStringContainsString('4800', $content);
    }

    public function test_esic_adhoc_csv_estimate_based_on_gross()
    {
        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        // single payroll gross 40000
        DB::table('payrolls')->insert([
            ['employee_id' => 1, 'period_start' => $start, 'period_end' => $end, 'gross' => 40000, 'tax' => 2000, 'net' => 38000, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $resp = $this->post('/api/finance/reports/statutory/download', [
            'type' => 'esic',
            'period_start' => $start,
            'period_end' => $end,
        ]);

    $resp->assertStatus(200);
    $this->assertStringContainsString('text/csv', $resp->headers->get('content-type'));
    $this->assertStringContainsString('attachment', strtolower($resp->headers->get('content-disposition') ?? ''));

        $content = $resp->getContent();
        // estimated_esic = gross * 0.0475 = 40000 * 0.0475 = 1900
        $this->assertStringContainsString('estimated_esic', $content);
        $this->assertStringContainsString('1900', $content);
    }
}
