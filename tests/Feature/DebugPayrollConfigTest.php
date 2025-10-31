<?php

namespace Tests\Feature;

use Tests\TestCase;

class DebugPayrollConfigTest extends TestCase
{
    public function test_runtime_config_override_applies()
    {
        config(['payroll.professional_tax' => [
            'kerala' => ['threshold' => 12000, 'amount' => 150.0],
        ]]);

        $mapping = config('payroll.professional_tax');

        $this->assertIsArray($mapping);
        $this->assertArrayHasKey('kerala', $mapping);
        $this->assertEquals(12000, $mapping['kerala']['threshold']);
        $this->assertEquals(150.0, $mapping['kerala']['amount']);
    }
}
