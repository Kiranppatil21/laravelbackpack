<?php

namespace Tests\Unit;

use Tests\TestCase;

class PayrollBindingTest extends TestCase
{
    public function test_container_resolves_wrapper()
    {
        $svc = $this->app->make(\App\Services\PayrollCalculator::class);
        $this->assertInstanceOf(\App\Services\PayrollCalculatorWrapper::class, $svc);
    }
}
