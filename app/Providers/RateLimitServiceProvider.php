<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        // Default for all web (Inertia/Blade) routes.
        RateLimiter::for('web', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(180)->by('web-user:'.$request->user()->getAuthIdentifier());
            }

            return Limit::perMinute(60)->by('web-ip:'.$request->ip());
        });

        // API routes (Sanctum /api/*).
        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(90)->by('api-user:'.$request->user()->getAuthIdentifier());
            }

            return Limit::perMinute(30)->by('api-ip:'.$request->ip());
        });

        // Login / credential guessing protection.
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return [
                Limit::perMinute(5)->by('login:'.$email.'|'.$request->ip()),
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });

        // Password change / reset style actions.
        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute(5)->by(
                'password:'.($request->user()?->getAuthIdentifier() ?: $request->ip())
            );
        });

        // Heavy PDF/Excel export & print endpoints.
        RateLimiter::for('exports', function (Request $request) {
            return Limit::perMinute(10)->by(
                'exports:'.($request->user()?->getAuthIdentifier() ?: $request->ip())
            );
        });

        // File / webcam uploads.
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(15)->by(
                'uploads:'.($request->user()?->getAuthIdentifier() ?: $request->ip())
            );
        });

        // Barcode / QR / lab scan endpoints.
        RateLimiter::for('scans', function (Request $request) {
            return Limit::perMinute(30)->by(
                'scans:'.($request->user()?->getAuthIdentifier() ?: $request->ip())
            );
        });
    }
}
