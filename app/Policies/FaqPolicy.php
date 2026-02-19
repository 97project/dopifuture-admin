<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaqPolicy
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
        return $user->hasPermissionTo('faqs.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->hasPermissionTo('faqs.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('faqs.create');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->hasPermissionTo('faqs.edit');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->hasPermissionTo('faqs.delete');
    }
}
