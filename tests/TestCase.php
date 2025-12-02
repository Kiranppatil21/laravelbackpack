<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Refresh the application between tests by default.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
