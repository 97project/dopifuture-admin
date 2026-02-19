<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactor
{
    /**
     * Enforce 2FA verification for web sessions.
     *
     * If the user has 2FA enabled but hasn't verified in this session,
     * redirect them to the 2FA verification page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Skip if 2FA is not enabled for the user
        if (!$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Skip if already verified in this session
        if (session('2fa_verified', false)) {
            return $next($request);
        }

        // Skip the 2FA verification route itself and logout
        $allowedRoutes = ['admin.2fa.verify', 'admin.2fa.challenge', 'admin.logout'];
        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        // For API requests, respond with JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => '2FA_REQUIRED',
                'message' => __('auth.2fa_required'),
            ], 403);
        }

        // Redirect to 2FA verification page
        return redirect()->route('admin.2fa.verify');
    }
}
