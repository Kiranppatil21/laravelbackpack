<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;

/**
 * Small shim so tests and legacy config can reference App\Http\Middleware\VerifyCsrfToken
 * while still delegating to the framework implementation. This satisfies static analysis
 * checks that expect the class to exist.
 */
class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    // Intentionally empty - we only need the class to exist and inherit framework behavior.
}
