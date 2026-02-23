<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Role Galaxy — İskelet Connector
 *
 * API endpoint'leri geldiğinde implemente edilecek.
 */
class RoleGalaxyConnector implements AppConnectorInterface
{
    public function syncUser(User $user): array
    {
        Log::channel('daily')->info('[RoleGalaxy] syncUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return ['success' => false, 'response' => null, 'error' => 'Not implemented'];
    }

    public function updateUser(User $user): array
    {
        Log::channel('daily')->info('[RoleGalaxy] updateUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return ['success' => false, 'response' => null, 'error' => 'Not implemented'];
    }

    public function removeUser(User $user): bool
    {
        Log::channel('daily')->info('[RoleGalaxy] removeUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return false;
    }

    public function getUser(User $user): ?array
    {
        return null;
    }
}
