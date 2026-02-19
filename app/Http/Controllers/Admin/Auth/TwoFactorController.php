<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use App\Models\User;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactorService)
    {
    }

    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('admin.auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail($userId);
        $code = $request->input('code');

        if (
            $this->twoFactorService->verifyCode($user, $code) ||
            $this->twoFactorService->verifyRecoveryCode($user, $code)
        ) {
            session()->forget('2fa_user_id');
            auth()->login($user);
            session(['locale' => $user->locale]);
            session(['2fa_verified' => true]);
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['code' => __('auth.2fa_code_invalid')]);
    }

    public function setup(Request $request)
    {
        $user = auth()->user();
        $secret = $this->twoFactorService->generateSecret();
        $qrUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);

        session(['2fa_setup_secret' => $secret]);

        return view('admin.auth.two-factor-setup', compact('secret', 'qrUrl'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $secret = session('2fa_setup_secret');
        if (!$secret) {
            return redirect()->route('admin.profile')
                ->with('error', __('admin.2fa_session_expired'));
        }

        $result = $this->twoFactorService->enable(auth()->user(), $secret, $request->input('code'));

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        session()->forget('2fa_setup_secret');

        return redirect()->route('admin.profile')
            ->with('success', __('admin.2fa_enabled'))
            ->with('recovery_codes', $result['recovery_codes']);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (!\Hash::check($request->input('password'), auth()->user()->password)) {
            return back()->withErrors(['password' => __('auth.password_incorrect')]);
        }

        $this->twoFactorService->disable(auth()->user());

        return redirect()->route('admin.profile')
            ->with('success', __('admin.2fa_disabled'));
    }

    public function regenerateRecoveryCodes()
    {
        $codes = $this->twoFactorService->regenerateRecoveryCodes(auth()->user());

        return redirect()->route('admin.profile')
            ->with('success', __('admin.recovery_codes_regenerated'))
            ->with('recovery_codes', $codes);
    }
}
