<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
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
        return $user->hasPermissionTo('menus.view');
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('menus.create');
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.edit');
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.delete');
    }
}
