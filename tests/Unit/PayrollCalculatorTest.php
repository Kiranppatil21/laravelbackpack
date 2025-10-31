<?php

namespace Tests\Unit;

use App\Services\PayrollCalculator;
use PHPUnit\Framework\TestCase;

class PayrollCalculatorTest extends TestCase
{
    public function test_low_income_no_tax()
    {
        $svc = new PayrollCalculator();
        // Monthly total that annualizes to <= 5,00,000 (e.g., 30k/month -> 360k/year)
        [$gross, $net, $breakdown] = $svc->compute(25000, 5000, 0.0, ['regime' => 'old']);

        $this->assertEquals(30000, $gross);
        $this->assertArrayHasKey('monthly_tax', $breakdown);
        $this->assertEquals(0, (int) $breakdown['monthly_tax']);
        $this->assertLessThanOrEqual($gross, $net);
    }

    public function test_middle_income_some_tax()
    {
        $svc = new PayrollCalculator();
        [$gross, $net, $breakdown] = $svc->compute(40000, 10000, 0.0, ['regime' => 'old']);

        $this->assertEquals(50000, $gross);
        $this->assertArrayHasKey('monthly_tax', $breakdown);
        $this->assertGreaterThan(0, $breakdown['monthly_tax']);
        $this->assertLessThan($gross, $net + $breakdown['monthly_tax']);
    }

    public function test_high_income_higher_tax_new_regime()
    {
        $svc = new PayrollCalculator();
        [$grossOld, $netOld, $bdOld] = $svc->compute(150000, 50000, 0.0, ['regime' => 'old']);
        [$grossNew, $netNew, $bdNew] = $svc->compute(150000, 50000, 0.0, ['regime' => 'new']);

        $this->assertEquals($grossOld, $grossNew);
        $this->assertArrayHasKey('monthly_tax', $bdOld);
        $this->assertArrayHasKey('monthly_tax', $bdNew);
        $this->assertGreaterThan(0, $bdOld['monthly_tax']);
        $this->assertGreaterThan(0, $bdNew['monthly_tax']);
    }
}
