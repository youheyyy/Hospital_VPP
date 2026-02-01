<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip active check for logout route to allow users to sign out
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        if (Auth::check() && !Auth::user()->active) {
            // Provide JSON response for API/Ajax
            if ($request->expectsJson()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->json(['message' => 'Tài khoản của bạn đã bị vô hiệu hóa.'], 403);
            }

            // Return Locked View for Web
            return response()->view('errors.account_locked');
        }

        return $next($request);
    }
}
