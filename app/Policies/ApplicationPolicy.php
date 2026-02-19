<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
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
        return $user->hasPermissionTo('applications.view');
    }

    public function view(User $user, Application $application): bool
    {
        return $user->hasPermissionTo('applications.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('applications.create');
    }

    public function update(User $user, Application $application): bool
    {
        return $user->hasPermissionTo('applications.edit');
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->hasPermissionTo('applications.delete');
    }
}
