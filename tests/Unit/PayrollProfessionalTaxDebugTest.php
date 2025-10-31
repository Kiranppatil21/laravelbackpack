<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PayrollCalculatorDebug;

class PayrollProfessionalTaxDebugTest extends TestCase
{
    public function test_professional_tax_mapping_override_debug()
    {
        // Remove previous debug file if present
        @unlink('/tmp/prof_tax_debug.log');

        $calc = new PayrollCalculatorDebug();

        $options = [
            'state' => 'kerala',
            'professional_tax_mapping' => [
                'kerala' => ['threshold' => 12000, 'amount' => 150.0],
            ],
        ];

        [$monthlyGross, $monthlyNet, $breakdown] = $calc->compute(13000, 0, 0, $options);

        // Ensure debug file was written and contains expected lines
        $this->assertFileExists('/tmp/prof_tax_debug.log');
        $contents = file_get_contents('/tmp/prof_tax_debug.log');
        $this->assertStringContainsString('computeProfessionalTax Debug called', $contents);

        // Expect professional tax amount to be applied
        $this->assertEquals(150.0, $breakdown['professional_tax_monthly']);
    }
}
