<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClickjackingProtectionTest extends TestCase
{
    public function test_responses_include_clickjacking_protection_headers(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy', "frame-ancestors 'self'");
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_home_response_cannot_be_framed_by_foreign_origins(): void
    {
        $response = $this->get('/');

        $xFrameOptions = $response->headers->get('X-Frame-Options');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($xFrameOptions);
        $this->assertTrue(
            in_array(strtoupper($xFrameOptions), ['DENY', 'SAMEORIGIN'], true),
            'X-Frame-Options must be DENY or SAMEORIGIN'
        );

        $this->assertNotNull($csp);
        $this->assertMatchesRegularExpression(
            "/frame-ancestors\s+('self'|none)/i",
            $csp,
            'CSP must restrict frame-ancestors to self or none'
        );
    }
}
