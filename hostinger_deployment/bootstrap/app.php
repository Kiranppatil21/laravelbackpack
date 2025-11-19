<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        
        // Register role middleware alias for Spatie Permission
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch exceptions globally
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Handle admin routes specifically
            if ($request->is('admin/*')) {
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Session expired. Please refresh the page and try again.',
                        'message' => 'Your session has expired. Please refresh the page to continue.',
                        'reload' => true,
                        'csrf_error' => true
                    ], 419);
                }
                
                // For regular form submissions and modal requests, redirect back with error
                return redirect()->back()
                    ->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->withErrors(['csrf' => 'Your session has expired. Please try again.'])
                    ->with('error', 'Session expired. Please refresh the page and try again.')
                    ->with('popup_error', '419 PAGE EXPIRED - Please refresh the page and try again.');
            }
            
            return null; // Let default handler take over for non-admin routes
        });
    })->create();
