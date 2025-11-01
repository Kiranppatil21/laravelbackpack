<?php

namespace Tests\Unit;

use App\Services\PayrollCalculator;
use PHPUnit\Framework\TestCase;

class PayrollProfessionalTaxTest extends TestCase
{
    public function test_maharashtra_professional_tax_applies()
    {
        $svc = new PayrollCalculator();
        [$gross, $net, $bd] = $svc->compute(16000, 0.0, 0.0, ['regime' => 'old', 'state' => 'maharashtra']);

        $this->assertGreaterThan(0, $bd['professional_tax_monthly']);
    }

    public function test_kerala_professional_tax_threshold()
    {
        $svc = new PayrollCalculator();
        // Below threshold
        [$g1, $n1, $b1] = $svc->compute(11000, 0.0, 0.0, ['state' => 'kerala']);
        $this->assertEquals(0.0, $b1['professional_tax_monthly']);

        // Above threshold
        [$g2, $n2, $b2] = $svc->compute(13000, 0.0, 0.0, ['state' => 'kerala']);
        $this->assertGreaterThan(0, $b2['professional_tax_monthly']);
    }
}
