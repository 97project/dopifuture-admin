<?php

namespace App\Policies;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationRequestPolicy
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
        return $user->hasPermissionTo('registration_requests.view');
    }
    public function view(User $user, RegistrationRequest $m): bool
    {
        return $user->hasPermissionTo('registration_requests.view');
    }
    public function update(User $user, RegistrationRequest $m): bool
    {
        return $user->hasPermissionTo('registration_requests.edit');
    }
    public function delete(User $user, RegistrationRequest $m): bool
    {
        return $user->hasPermissionTo('registration_requests.delete');
    }
}
