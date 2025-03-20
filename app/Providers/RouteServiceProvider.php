<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin routes
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        });

        // Register global route patterns
        Route::pattern('id', '[0-9]+');
        Route::pattern('slug', '[a-z0-9-]+');

        // Register route model bindings
        Route::bind('post', function ($value) {
            return \App\Models\Post::where('id', $value)
                ->orWhere('slug', $value)
                ->firstOrFail();
        });

        Route::bind('category', function ($value) {
            return \App\Models\Category::where('id', $value)
                ->orWhere('slug', $value)
                ->firstOrFail();
        });

        Route::bind('tag', function ($value) {
            return \App\Models\Tag::where('id', $value)
                ->orWhere('slug', $value)
                ->firstOrFail();
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Admin panel rate limiting
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Media upload rate limiting
        RateLimiter::for('media-upload', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}