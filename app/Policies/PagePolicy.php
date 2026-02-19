<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
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
        return $user->hasPermissionTo('pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->hasPermissionTo('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->hasPermissionTo('pages.edit');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->hasPermissionTo('pages.delete');
    }
}
