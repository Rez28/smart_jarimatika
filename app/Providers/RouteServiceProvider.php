<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route; // <-- IMPORT YANG KRUSIAL

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     * Biasanya di-redirect setelah login.
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            
            // BLOK API (untuk api/scores)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // BLOK WEB
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}