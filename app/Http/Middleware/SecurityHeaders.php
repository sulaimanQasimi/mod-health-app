<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers that mitigate clickjacking and related embedding attacks.
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

        return $response;
    }
}
