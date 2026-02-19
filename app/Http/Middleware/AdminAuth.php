<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('admin.login')
                ->with('error', __('admin.account_inactive'));
        }

        if ($user->isLocked()) {
            auth()->logout();
            return redirect()->route('admin.login')
                ->with('error', __('admin.account_locked'));
        }

        return $next($request);
    }
}
