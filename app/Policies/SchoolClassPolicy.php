<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolClassPolicy
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
        return $user->hasPermissionTo('classes.view');
    }
    public function view(User $user, SchoolClass $m): bool
    {
        return $user->hasPermissionTo('classes.view');
    }
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('classes.create');
    }
    public function update(User $user, SchoolClass $m): bool
    {
        return $user->hasPermissionTo('classes.edit');
    }
    public function delete(User $user, SchoolClass $m): bool
    {
        return $user->hasPermissionTo('classes.delete');
    }
}
