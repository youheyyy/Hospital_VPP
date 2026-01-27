<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Assuming user has a 'role' column or method to check role
        // Adjust logic based on how roles are stored (e.g., column 'role' = 'ADMIN' or 'DEPARTMENT')

        $userRole = strtoupper($user->role_code ?? '');

        if ($userRole !== strtoupper($role)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
