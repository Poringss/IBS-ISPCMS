<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Normalize role value
        $userRole = strtolower(trim(Auth::user()->role ?? ''));

        // Normalize allowed roles too
        $allowed = array_map(fn($r) => strtolower(trim((string) $r)), $roles);

        if (!in_array($userRole, $allowed, true)) {
            if (config('app.debug')) {
                abort(403, "Unauthorized. Your role is '{$userRole}', allowed: [" . implode(',', $allowed) . "]");
            }
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
