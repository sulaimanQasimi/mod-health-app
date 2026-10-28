<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_responses_include_enterprise_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy', "frame-ancestors 'self'");
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=(self)', (string) $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('geolocation=()', (string) $response->headers->get('Permissions-Policy'));
    }

    public function test_hsts_is_not_sent_over_insecure_http(): void
    {
        $response = $this->get('/login');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $response = $this->get('https://localhost/login');

        $response->assertHeader(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );
    }
}
