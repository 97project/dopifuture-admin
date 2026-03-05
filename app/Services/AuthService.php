<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    public function attemptLogin(string $email, string $password, ?string $ip = null): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['success' => false, 'error' => 'invalid_credentials', 'message' => __('auth.failed')];
        }

        if ($user->isLocked()) {
            $minutes = max(1, (int) ceil($user->locked_until->diffInMinutes(now(), false)));
            return ['success' => false, 'error' => 'account_locked', 'message' => __('auth.locked', ['minutes' => $minutes])];
        }

        if ($user->status !== 'active') {
            return ['success' => false, 'error' => 'account_inactive', 'message' => __('auth.inactive')];
        }

        if (!Hash::check($password, $user->password)) {
            $this->handleFailedLogin($user);
            return ['success' => false, 'error' => 'invalid_credentials', 'message' => __('auth.failed')];
        }

        $this->handleSuccessfulLogin($user, $ip);
        return ['success' => true, 'user' => $user];
    }

    protected function handleFailedLogin(User $user): void
    {
        $maxAttempts = (int) Setting::getValue('security', 'max_login_attempts', 5);
        $lockoutMinutes = (int) Setting::getValue('security', 'lockout_minutes', 30);

        $user->increment('failed_login_count');

        if ($user->failed_login_count >= $maxAttempts) {
            $user->update(['locked_until' => now()->addMinutes($lockoutMinutes)]);
            ActivityLog::log('account_locked', 'auth', $user, [
                'reason' => 'max_failed_attempts',
                'attempts' => $user->failed_login_count,
            ]);
        }

        ActivityLog::log('login_failed', 'auth', $user);
    }

    protected function handleSuccessfulLogin(User $user, ?string $ip): void
    {
        $user->update([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);

        ActivityLog::log('login_success', 'auth', $user);
    }

    public function createApiToken(User $user, string $name, array $abilities = ['*']): string
    {
        $token = $user->createToken($name, $abilities);
        ActivityLog::log('token_created', 'auth', $user, ['token_name' => $name]);
        return $token->plainTextToken;
    }

    public function revokeApiToken(User $user, int $tokenId): bool
    {
        $token = $user->tokens()->find($tokenId);
        if (!$token) {
            return false;
        }
        $token->delete();
        ActivityLog::log('token_revoked', 'auth', $user, ['token_name' => $token->name]);
        return true;
    }

    public function logout(User $user): void
    {
        ActivityLog::log('logout', 'auth', $user);
    }

    public function verifyRecaptcha(?string $token): bool
    {
        $enabled = Setting::getValue('security', 'recaptcha_enabled', false);
        if (!$enabled) {
            return true;
        }

        if (!$token) {
            return false;
        }

        $secret = Setting::getValue('security', 'recaptcha_secret_key');
        if (!$secret) {
            return true;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ]);
            $result = $response->json();
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            return true;
        }
    }
}
