<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PayrollCalculator;

class PayrollProfessionalTaxFeatureTest extends TestCase
{
    public function test_professional_tax_respects_config()
    {
        // Ensure deterministic config for the test
        config(['payroll.professional_tax' => [
            'kerala' => ['threshold' => 12000, 'amount' => 150.0],
        ]]);

        // Resolve via container so config() helper is available to service
        $svc = $this->app->make(PayrollCalculator::class);

        // Below threshold -> zero
        [$g1, $n1, $b1] = $svc->compute(11000, 0.0, 0.0, ['state' => 'kerala']);
        $this->assertEquals(0.0, $b1['professional_tax_monthly']);

        // Above threshold -> configured amount
        [$g2, $n2, $b2] = $svc->compute(13000, 0.0, 0.0, ['state' => 'kerala']);
        $this->assertEquals(150.0, $b2['professional_tax_monthly']);
    }
}
