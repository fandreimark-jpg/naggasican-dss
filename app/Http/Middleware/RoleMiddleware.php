<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * RoleMiddleware
 *
 * Checks if the authenticated user has the required role
 * before allowing access to a route.
 *
 * Usage in routes:
 * Route::middleware(['auth', 'role:principal'])->group(...)
 * Route::middleware(['auth', 'role:adviser'])->group(...)
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        // Redirect to login if not authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Block access if role does not match
        // Returns 403 Forbidden instead of redirecting
        if (auth()->user()->role !== $role) {
            abort(403, 'Unauthorized.');
        }

        // User is authenticated and has correct role — allow request
        return $next($request);
    }
}