<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalAuthController extends Controller
{
    /**
     * Show the portal login form (separate from admin login).
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    /**
     * Handle portal login submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Block admin-level roles from portal
            $portalRoles = ['school-admin', 'school-principal', 'teacher', 'student'];
            if (!$user->hasAnyRole($portalRoles)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => __('auth.no_portal_access'),
                    ]);
            }

            // Set user locale preference in session
            if ($user->locale) {
                session(['locale' => $user->locale]);
                app()->setLocale($user->locale);
            }

            return redirect()->intended(route('portal.dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => __('auth.failed'),
            ]);
    }

    /**
     * Handle portal logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.home');
    }
}
