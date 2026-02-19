<?php

namespace App\Policies;

use App\Models\License;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LicensePolicy
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
        return $user->hasPermissionTo('licenses.view');
    }
    public function view(User $user, License $m): bool
    {
        return $user->hasPermissionTo('licenses.view');
    }
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('licenses.create');
    }
    public function update(User $user, License $m): bool
    {
        return $user->hasPermissionTo('licenses.edit');
    }
    public function delete(User $user, License $m): bool
    {
        return $user->hasPermissionTo('licenses.delete');
    }
}
