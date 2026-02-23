<?php

namespace App\Connectors;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Role Galaxy — Henüz Entegrasyon Yapılmadı
 *
 * API endpoint'leri geldiğinde implemente edilecek.
 */
class RoleGalaxyConnector implements AppConnectorInterface
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
        return true; // henüz kayıt yok, sorun değil
    }

    public function getUser(User $user): ?array
    {
        return null;
    }

    /**
     * Bu connector'ın hazır olup olmadığını bildir.
     */
    public static function isReady(): bool
    {
        return false;
    }
}
