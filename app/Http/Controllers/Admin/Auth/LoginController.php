<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        if (!$this->authService->verifyRecaptcha($request->input('recaptcha_token'))) {
            return back()->withErrors(['recaptcha' => __('auth.recaptcha_failed')])->withInput();
        }

        $result = $this->authService->attemptLogin(
            $request->input('email'),
            $request->input('password'),
            $request->ip()
        );

        if (!$result['success']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        $user = $result['user'];

        if ($user->hasTwoFactorEnabled()) {
            session(['2fa_user_id' => $user->id]);
            return redirect()->route('admin.2fa.challenge');
        }

        auth()->login($user, $request->boolean('remember'));
        session(['locale' => $user->locale]);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->authService->logout(auth()->user());
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
