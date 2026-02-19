<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormPolicy
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
        return $user->hasPermissionTo('forms.view');
    }

    public function view(User $user, Form $form): bool
    {
        return $user->hasPermissionTo('forms.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('forms.create');
    }

    public function update(User $user, Form $form): bool
    {
        return $user->hasPermissionTo('forms.edit');
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->hasPermissionTo('forms.delete');
    }

    public function viewSubmissions(User $user): bool
    {
        return $user->hasPermissionTo('forms.view');
    }
}
