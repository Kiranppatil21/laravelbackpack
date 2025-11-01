<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PayrollCalculator;

class PayrollProfessionalTaxDirectInstantiationTest extends TestCase
{
    public function test_professional_tax_mapping_override_direct_instantiation()
    {
        $calc = new PayrollCalculator();

        $options = [
            'state' => 'kerala',
            'professional_tax_mapping' => [
                'kerala' => ['threshold' => 12000, 'amount' => 150.0],
            ],
        ];

        [$monthlyGross, $monthlyNet, $breakdown] = $calc->compute(13000, 0, 0, $options);

        $this->assertEquals(150.0, $breakdown['professional_tax_monthly']);
    }
}
