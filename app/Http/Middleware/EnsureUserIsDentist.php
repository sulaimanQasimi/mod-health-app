<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsDentist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has an associated doctor
        $doctor = $user->doctor;

        // Check if the doctor exists and is marked as a dentist
        if (!$doctor) {
            abort(403, 'Access denied. You must be associated with a doctor account to access this resource.');
        }

        if (!$doctor->is_dentist) {
            abort(403, 'Access denied. Only dentists can access this resource.');
        }

        return $next($request);
    }
}
