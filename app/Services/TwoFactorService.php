<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ActivityLog;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeUrl(User $user, string $secret): string
    {
        $appName = config('app.name', 'Panel26');
        return $this->google2fa->getQRCodeUrl($appName, $user->email, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function enable(User $user, string $secret, string $code): array
    {
        if (!$this->verify($secret, $code)) {
            return ['success' => false, 'message' => __('auth.2fa_code_invalid')];
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        ActivityLog::log('2fa_enabled', 'auth', $user);

        return [
            'success' => true,
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        ActivityLog::log('2fa_disabled', 'auth', $user);
    }

    public function verifyCode(User $user, string $code): bool
    {
        $secret = decrypt($user->two_factor_secret);
        return $this->verify($secret, $code);
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes;
        if (!is_array($codes)) {
            return false;
        }

        $index = array_search($code, $codes);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->update(['two_factor_recovery_codes' => array_values($codes)]);

        ActivityLog::log('2fa_recovery_used', 'auth', $user);

        return true;
    }

    protected function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(5) . '-' . Str::random(5);
        }
        return $codes;
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $codes]);
        ActivityLog::log('2fa_recovery_regenerated', 'auth', $user);
        return $codes;
    }
}
