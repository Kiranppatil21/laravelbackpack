<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFormPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!backpack_user()) {
            return redirect()->route('backpack.auth.login');
        }

        // Super Admin has all permissions
        if (backpack_user()->hasRole('Super Admin')) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!backpack_user()->can($permission)) {
            abort(403, 'You do not have permission to access this form.');
        }

        return $next($request);
    }
}
