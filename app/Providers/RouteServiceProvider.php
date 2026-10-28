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
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $username = (string) $request->input('email', $request->ip());

            return [
                Limit::perMinute(5)->by($request->ip().'|'.strtolower($username)),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email', $request->ip());

            return [
                Limit::perMinute(3)->by($request->ip().'|'.strtolower($email)),
                Limit::perMinute(10)->by($request->ip()),
            ];
        });


        // Route model bindings
        Route::model('review', \App\Models\PhysiotherapyProcedureReview::class);
        Route::model('physiotherapyProcedure', \App\Models\PhysiotherapyProcedure::class);

        Route::bind('bloodUnit', function ($value) {
            if (! auth()->check()) {
                abort(403);
            }

            return \App\Models\BloodUnit::query()
                ->where('id', $value)
                ->where('branch_id', auth()->user()->branch_id)
                ->firstOrFail();
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
