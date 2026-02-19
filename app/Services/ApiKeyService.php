<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function generate(int $userId, string $name, array $abilities = ['*'], array $options = []): array
    {
        $plainKey = 'pk_' . Str::random(40);
        $keyHash = hash('sha256', $plainKey);
        $keyPrefix = substr($plainKey, 0, 8);

        $apiKey = ApiKey::create([
            'user_id' => $userId,
            'name' => $name,
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
            'abilities' => $abilities,
            'ip_restrictions' => $options['ip_restrictions'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
        ]);

        ActivityLog::log('api_key_created', 'api_key', $apiKey, ['name' => $name]);

        return [
            'api_key' => $apiKey,
            'plain_key' => $plainKey,
        ];
    }

    public function validate(string $plainKey): ?ApiKey
    {
        $keyHash = hash('sha256', $plainKey);
        $apiKey = ApiKey::where('key_hash', $keyHash)->first();

        if (!$apiKey || !$apiKey->isValid()) {
            return null;
        }

        return $apiKey;
    }

    public function rotate(ApiKey $apiKey): array
    {
        $plainKey = 'pk_' . Str::random(40);
        $keyHash = hash('sha256', $plainKey);
        $keyPrefix = substr($plainKey, 0, 8);

        $apiKey->update([
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
        ]);

        ActivityLog::log('api_key_rotated', 'api_key', $apiKey, ['name' => $apiKey->name]);

        return [
            'api_key' => $apiKey->fresh(),
            'plain_key' => $plainKey,
        ];
    }

    public function revoke(ApiKey $apiKey): void
    {
        $apiKey->update(['is_active' => false]);
        ActivityLog::log('api_key_revoked', 'api_key', $apiKey, ['name' => $apiKey->name]);
    }

    public function delete(ApiKey $apiKey): void
    {
        ActivityLog::log('api_key_deleted', 'api_key', $apiKey, ['name' => $apiKey->name]);
        $apiKey->delete();
    }
}
