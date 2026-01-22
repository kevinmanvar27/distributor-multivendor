<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetTheme;
use App\Http\Middleware\SiteManagement;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\FrontendRedirectIfAuthenticated;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ForceJsonResponse;

use App\Http\Middleware\VendorMiddleware;
use App\Http\Middleware\CheckVendorPermission;
use App\Http\Middleware\EnsureVendorCustomer;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/vendor.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetTheme::class,
            SiteManagement::class,
        ]);
        
        // Force JSON responses for all API routes
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
        
        // Register middleware aliases
        $middleware->alias([
            'auth' => Authenticate::class,
            'permission' => CheckPermission::class,
            'frontend.guest' => FrontendRedirectIfAuthenticated::class,
            'frontend.access' => \App\Http\Middleware\CheckFrontendAccess::class,
            'vendor' => VendorMiddleware::class,
            'vendor.permission' => CheckVendorPermission::class,
            'vendor.customer' => EnsureVendorCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();