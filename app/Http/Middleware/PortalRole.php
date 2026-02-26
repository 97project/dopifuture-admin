<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal Role Guard — only school staff and students can access the portal.
 * Admin-level roles are redirected to the admin panel.
 */
class PortalRole
{
    /** Roles allowed on the portal side */
    private const ALLOWED_ROLES = [
        'school-admin',
        'school-principal',
        'teacher',
        'student',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('portal.login');
        }

        // If user has ANY of the allowed portal roles → let them through
        if ($user->hasAnyRole(self::ALLOWED_ROLES)) {
            return $next($request);
        }

        // Admin-level users → redirect to admin dashboard
        if ($user->hasAnyRole(['super-admin', 'admin', 'moderator', 'editor', 'license-manager'])) {
            return redirect()->route('admin.dashboard')
                ->with('info', __('admin.portal_access_denied'));
        }

        // No matching role at all
        abort(403, __('auth.no_portal_access'));
    }
}
