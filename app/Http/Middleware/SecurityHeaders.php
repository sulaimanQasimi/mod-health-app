<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers that mitigate clickjacking and related browser attacks.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Legacy framing control (supported by older browsers).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);

        // Modern framing control; overrides X-Frame-Options when both are present.
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'",
            false
        );

        // Reduce MIME-sniffing risk on HTML responses.
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);

        // Limit referrer leakage to third parties.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);

        // Disable unused powerful browser features; allow camera for QR/webcam flows.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            false
        );

        // HSTS only over HTTPS so local HTTP development is not broken.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
                false
            );
        }

        return $response;
    }
}
