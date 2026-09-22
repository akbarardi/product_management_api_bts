<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        // Rate limiter: product writes — 1 per 5 seconds per authenticated user
        RateLimiter::for('product-write', function (Request $request) {
            return Limit::perSecond(1, 5)->by($request->user('api')?->id ?: $request->ip());
        });

        // Rate limiter: auth routes — 3 per 60 seconds per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
