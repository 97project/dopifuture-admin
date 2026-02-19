<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
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
        return $user->hasPermissionTo('media.view');
    }

    public function view(User $user, Media $media): bool
    {
        return $user->hasPermissionTo('media.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('media.upload');
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->hasPermissionTo('media.delete');
    }
}
