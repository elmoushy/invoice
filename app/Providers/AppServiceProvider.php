<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ignore Sanctum migrations if using default table structure
        Sanctum::ignoreMigrations();
    }
}
