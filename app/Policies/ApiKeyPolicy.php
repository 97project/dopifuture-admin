<?php

namespace App\Policies;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiKeyPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('api-keys.view');
    }

    public function view(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id || $user->hasPermissionTo('api-keys.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('api-keys.create');
    }

    public function update(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id || $user->hasPermissionTo('api-keys.edit');
    }

    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id || $user->hasPermissionTo('api-keys.delete');
    }

    public function rotate(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id || $user->hasPermissionTo('api-keys.edit');
    }

    public function revoke(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id || $user->hasPermissionTo('api-keys.edit');
    }
}
