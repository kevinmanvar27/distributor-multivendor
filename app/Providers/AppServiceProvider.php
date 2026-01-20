<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;

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
        // Load helper functions
        foreach (glob(app_path('Helpers') . '/*.php') as $file) {
            require_once $file;
        }

        // Register Product Observer for low stock notifications
        Product::observe(ProductObserver::class);
    }
}