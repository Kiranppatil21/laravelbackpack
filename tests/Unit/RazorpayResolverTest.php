<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RazorpayResolver;

class RazorpayResolverTest extends TestCase
{
    public function test_returns_container_bound_instance()
    {
        $fake = new \stdClass();
        // bind under the common FQCN key
        app()->bind('Razorpay\\Api\\Api', fn() => $fake);

        $resolver = new RazorpayResolver();
        $result = $resolver->resolve();

        $this->assertSame($fake, $result);
    }

    public function test_returns_null_when_not_bound_and_no_env()
    {
        // clear env keys for test to avoid accidental instantiation
        putenv('RAZORPAY_KEY_ID=');
        putenv('RAZORPAY_KEY_SECRET=');

        // ensure no binding exists
        if (app()->bound('Razorpay\\Api\\Api')) {
            // rebind a closure that returns null to simulate unbound state
            app()->bind('Razorpay\\Api\\Api', fn() => null);
        }

        $resolver = new RazorpayResolver();
        $result = $resolver->resolve();

        $this->assertNull($result);
    }
}
