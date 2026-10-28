<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginThrottlingTest extends TestCase
{
    public function test_login_rate_limiter_is_configured(): void
    {
        $limiter = RateLimiter::limiter('login');

        $this->assertIsCallable($limiter);

        $limits = $limiter(Request::create('/login', 'POST', [
            'email' => 'attacker@example.com',
        ]));

        $this->assertIsArray($limits);
        $this->assertNotEmpty($limits);
        $this->assertContainsOnlyInstancesOf(Limit::class, $limits);
    }

    public function test_password_reset_rate_limiter_is_configured(): void
    {
        $limiter = RateLimiter::limiter('password-reset');

        $this->assertIsCallable($limiter);

        $limits = $limiter(Request::create('/password/email', 'POST', [
            'email' => 'user@example.com',
        ]));

        $this->assertIsArray($limits);
        $this->assertNotEmpty($limits);
        $this->assertContainsOnlyInstancesOf(Limit::class, $limits);
    }
}
