<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Study Space — İskelet Connector
 *
 * API endpoint'leri geldiğinde implemente edilecek.
 */
class StudySpaceConnector implements AppConnectorInterface
{
    public function syncUser(User $user): array
    {
        Log::channel('daily')->info('[StudySpace] syncUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return ['success' => false, 'response' => null, 'error' => 'Not implemented'];
    }

    public function updateUser(User $user): array
    {
        Log::channel('daily')->info('[StudySpace] updateUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return ['success' => false, 'response' => null, 'error' => 'Not implemented'];
    }

    public function removeUser(User $user): bool
    {
        Log::channel('daily')->info('[StudySpace] removeUser — henüz implemente edilmedi', ['userId' => $user->id]);
        return false;
    }

    public function getUser(User $user): ?array
    {
        return null;
    }
}
