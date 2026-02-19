<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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
        return $user->hasPermissionTo('posts.view');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasPermissionTo('posts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasPermissionTo('posts.edit');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasPermissionTo('posts.delete');
    }
}
