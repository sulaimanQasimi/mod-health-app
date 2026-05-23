<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNephrologist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $doctor = $user->doctor;

        if (!$doctor) {
            abort(403, 'Access denied. You must be associated with a doctor account to access this resource.');
        }

        if (!$doctor->is_nephrologist) {
            abort(403, 'Access denied. Only nephrologists can access this resource.');
        }

        return $next($request);
    }
}
