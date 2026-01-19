<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;

/**
 * Small shim so tests and legacy config can reference App\Http\Middleware\VerifyCsrfToken
 * while still delegating to the framework implementation. This satisfies static analysis
 * checks that expect the class to exist and inherit framework behavior.
 */
class VerifyCsrfToken extends BaseVerifyCsrfToken
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'admin/refresh-csrf',
        'admin/attendance/search',
        'admin/payroll/search',
        'admin/*/search', // General pattern for all search endpoints
        'admin/*/*/search', // Nested search endpoints
    ];
}
