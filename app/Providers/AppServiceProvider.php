<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the RazorpayResolver interface to the concrete implementation so it can be injected and mocked in tests
        $this->app->singleton(\App\Services\RazorpayResolverInterface::class, function ($app) {
            return new \App\Services\RazorpayResolver;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Ensure routes/api.php is loaded in projects that don't include a custom RouteServiceProvider
        $apiRoutes = base_path('routes/api.php');
        if (file_exists($apiRoutes)) {
            Route::middleware('api')->prefix('api')->group($apiRoutes);
        }
        // Ensure a 'role' middleware alias exists so role-based guards in routes don't cause BindingResolutionException
        $router = $this->app->make('\Illuminate\Routing\Router');
        if (class_exists('\Spatie\Permission\Middlewares\RoleMiddleware')) {
            $router->aliasMiddleware('role', \Spatie\Permission\Middlewares\RoleMiddleware::class);
        } else {
            // Fallback to a no-op middleware present in the app
            $router->aliasMiddleware('role', \App\Http\Middleware\AllowRole::class);
        }
    }
}
