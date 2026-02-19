<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolPolicy
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
        return $user->hasPermissionTo('schools.view');
    }
    public function view(User $user, School $m): bool
    {
        return $user->hasPermissionTo('schools.view');
    }
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('schools.create');
    }
    public function update(User $user, School $m): bool
    {
        return $user->hasPermissionTo('schools.edit');
    }
    public function delete(User $user, School $m): bool
    {
        return $user->hasPermissionTo('schools.delete');
    }
}
