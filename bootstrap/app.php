<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });

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
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustProxies::class,
        );

        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        );

        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\TrimStrings::class,
        );

        $middleware->replaceInGroup(
            'web',
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\EncryptCookies::class,
        );

        $middleware->preventRequestForgery(except: [
            'api/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->throttleApi();

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'dentist' => \App\Http\Middleware\EnsureUserIsDentist::class,
            'nephrologist' => \App\Http\Middleware\EnsureUserIsNephrologist::class,
            'pharmacy_role' => \App\Http\Middleware\EnsurePharmacyRole::class,
        ]);

        $middleware->redirectUsersTo('/home');
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('blood-bank:archive-expired')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })
    ->create();
