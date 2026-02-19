<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
        return $user->hasPermissionTo('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.delete');
    }
}
