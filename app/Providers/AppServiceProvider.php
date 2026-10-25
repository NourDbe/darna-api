<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PropertyOwnershipChecker;
use App\Services\DefaultPropertyOwnershipChecker;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   public function register(): void
{
    $this->app->bind(
        PropertyOwnershipChecker::class,
        DefaultPropertyOwnershipChecker::class
    );
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
