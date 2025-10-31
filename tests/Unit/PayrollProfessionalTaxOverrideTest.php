<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PayrollCalculator;

class PayrollProfessionalTaxOverrideTest extends TestCase
{
    public function test_professional_tax_mapping_override_used()
    {
        $svc = $this->app->make(PayrollCalculator::class);

        [$g, $n, $b] = $svc->compute(13000.0, 0.0, 0.0, [
            'state' => 'kerala',
            'professional_tax_mapping' => [
                'kerala' => ['threshold' => 12000, 'amount' => 150.0],
            ],
        ]);

        $this->assertEquals(150.0, $b['professional_tax_monthly']);
    }
}
