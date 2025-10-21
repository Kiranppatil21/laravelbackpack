<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Client;
use App\Observers\ClientObserver;

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
        if (class_exists(\App\Models\Client::class) && class_exists(\App\Observers\ClientObserver::class)) {
            \App\Models\Client::observe(\App\Observers\ClientObserver::class);
        }
    }
}
