<?php

namespace App\Policies;

use App\Models\NotificationTemplate;
use App\Models\User;

class NotificationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('notifications.view');
    }

    public function view(User $user, NotificationTemplate $template): bool
    {
        return $user->hasPermissionTo('notifications.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('notifications.create');
    }

    public function update(User $user, NotificationTemplate $template): bool
    {
        return $user->hasPermissionTo('notifications.edit');
    }

    public function delete(User $user, NotificationTemplate $template): bool
    {
        return $user->hasPermissionTo('notifications.delete');
    }

    public function send(User $user): bool
    {
        return $user->hasPermissionTo('notifications.send');
    }
}
