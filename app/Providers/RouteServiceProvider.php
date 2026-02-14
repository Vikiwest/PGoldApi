<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Middleware\ThrottleRequests;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // API Routes - Minimal middleware (NO CSRF for stateless token auth)
            //  Try without throttling first to debug the 302 issue
            Route::middleware([
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web Routes - Full middleware stack (WITH CSRF for session-based auth)
            Route::middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ])
                ->group(base_path('routes/web.php'));
        });
    }
}
