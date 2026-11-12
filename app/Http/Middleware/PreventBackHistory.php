<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Stop browsers from serving authenticated pages from cache via Back/Forward.
     * Forces a new request so auth middleware can redirect guests to login.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Cache-Control',
            'no-cache, no-store, max-age=0, must-revalidate',
            false
        );
        $response->headers->set('Pragma', 'no-cache', false);
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT', false);

        return $response;
    }
}
