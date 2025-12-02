<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Client;

class ClientGrossMarginTest extends TestCase
{
    /**
     * @dataProvider grossMarginProvider
     */
    public function test_gross_margin_computation(float $billing, float $salary, $esi, $pf, float $licensing, float $overhead, float $expected)
    {
        $client = new Client();
        $client->billing_rate = $billing;
        $client->salary_cost = $salary;
        $client->esi_rate = $esi;
        $client->pf_rate = $pf;
        $client->licensing_cost = $licensing;
        $client->administrative_overhead = $overhead;

        // Allow small floating point differences
        $this->assertEquals($expected, $client->gross_margin, "Gross margin mismatch", 0.01);
    }

    public static function grossMarginProvider(): array
    {
        return [
            // Simple case: same sample as script
            'basic' => [50000.00, 20000.00, 1.75, 12.00, 1000.00, 500.00, 25750.00],
            // No esi/pf
            'no_social' => [10000.00, 3000.00, null, null, 200.00, 100.00, 6700.00],
            // Zero costs
            'free' => [1000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1000.00],
            // Large rates
            'large_rates' => [20000.00, 5000.00, 5.00, 10.00, 250.00, 125.00, 20000.00 - (5000 + 250 + 125 + (5000*0.05) + (5000*0.10))],
            // fractional esi/pf
            'fractional' => [12345.67, 2345.89, 0.75, 3.25, 50.50, 25.25, round(12345.67 - (2345.89 + 50.50 + 25.25 + (2345.89*0.0075) + (2345.89*0.0325)), 2)],
        ];
    }
}
