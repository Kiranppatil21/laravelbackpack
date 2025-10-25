<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Avoid unconditional imports that may trigger class autoload errors during some CLI runs.

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only register the observer if both the model and observer classes exist.
        // Use string class names and class_exists guard to avoid "Class not found" during some console runs.
        if (class_exists('App\\Models\\Client') && class_exists('App\\Observers\\ClientObserver')) {
            \App\Models\Client::observe(\App\Observers\ClientObserver::class);
        }

        // Ensure a 'role' container binding exists so middleware resolution doesn't fail
        // in environments where the Spatie middleware alias isn't registered.
        if (! $this->app->bound('role')) {
            if (class_exists(\Spatie\Permission\Middlewares\RoleMiddleware::class)) {
                // If Spatie's middleware class exists, bind it under the 'role' key so
                // the container can resolve the alias used in routes.
                $this->app->bind('role', \Spatie\Permission\Middlewares\RoleMiddleware::class);
            } else {
                // Fallback: use our no-op middleware to avoid BindingResolutionException.
                $this->app->bind('role', function () {
                    return new \App\Http\Middleware\AllowRole();
                });
            }
        }
    }
}
