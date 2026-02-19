<?php

namespace App\Services;

use App\Mail\AccountDeletionConfirmation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AccountDeletionService
{
    /**
     * Request account deletion — sends confirmation email with signed URL.
     */
    public function requestDeletion(User $user): void
    {
        $confirmUrl = URL::temporarySignedRoute(
            'account.delete.confirm',
            now()->addHours(24),
            ['user' => $user->id]
        );

        Mail::to($user->email)->send(new AccountDeletionConfirmation($user, $confirmUrl));

        Log::info('Account deletion requested', ['user_id' => $user->id]);
    }

    /**
     * Execute account deletion: anonymize PII and soft-delete.
     */
    public function executeDelete(User $user): void
    {
        // Revoke all tokens
        $user->tokens()->delete();

        // Anonymize PII
        $user->update([
            'name' => 'Deleted',
            'surname' => 'User',
            'email' => "deleted_{$user->id}@removed.local",
            'phone' => null,
            'avatar_path' => null,
            'device_token' => null,
            'device_platform' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'status' => 'inactive',
        ]);

        // Soft-delete
        $user->delete();

        Log::info("Account deleted and anonymized", ['user_id' => $user->id]);
    }

    /**
     * Confirm deletion from API (with password verification).
     */
    public function confirmAndDelete(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        $this->executeDelete($user);
        return true;
    }
}
