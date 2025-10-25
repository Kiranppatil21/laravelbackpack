<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * A no-op fallback middleware for the 'role' alias.
 * When the Spatie Role middleware isn't available (e.g., in stripped test/cli environments),
 * binding this class under the 'role' key prevents container resolution errors.
 */
class AllowRole
{
    /**
     * Handle an incoming request.
     * Simply pass-through to the next middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        return $next($request);
    }
}
