<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Way AI Coach — Henüz Entegrasyon Yapılmadı
 *
 * API endpoint'leri geldiğinde implemente edilecek.
 */
class WayAiCoachConnector implements AppConnectorInterface
{
    public function syncUser(User $user): array
    {
        return ['success' => false, 'response' => null, 'error' => 'not_ready'];
    }

    public function updateUser(User $user): array
    {
        return ['success' => false, 'response' => null, 'error' => 'not_ready'];
    }

    public function removeUser(User $user): bool
    {
        return true;
    }

    public function getUser(User $user): ?array
    {
        return null;
    }

    public static function isReady(): bool
    {
        return false;
    }
}
