<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $value = true; // keep test trivial; suppress phpstan warning on the assertion
        // @phpstan-ignore-next-line
        $this->assertTrue($value);
    }
}
