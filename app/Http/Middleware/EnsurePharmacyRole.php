<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePharmacyRole
{
    /**
     * Ensure the user has at least one active pharmacy with one of the allowed roles.
     *
     * Usage: ->middleware('pharmacy_role:manager,staff')
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // System admins bypass pharmacy-role checks
        if ($user->hasRole(['admin', 'super_admin'])) {
            return $next($request);
        }

        $allowedRoles = array_values(array_filter(array_map('trim', $allowedRoles)));

        if (empty($allowedRoles)) {
            abort(403, 'No allowed pharmacy roles configured.');
        }

        // If the user is not assigned to any pharmacy, don't enforce pharmacy-role restrictions here.
        // This keeps non-pharmacy modules/users working as before.
        if (!$user->activePharmacies()->wherePivot('is_active', true)->exists()) {
            return $next($request);
        }

        if (!$user->hasActivePharmacyRole($allowedRoles)) {
            abort(403);
        }

        return $next($request);
    }
}

