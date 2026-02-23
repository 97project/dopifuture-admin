<?php

namespace App\Connectors;

use App\Models\User;

/**
 * Tüm uygulama connector'larının uyması gereken arayüz.
 *
 * Her uygulama (mission-way, way-startup, role-galaxy vb.) bu arayüzü
 * implemente eden kendi connector sınıfına sahiptir.
 */
interface AppConnectorInterface
{
    /**
     * Kullanıcıyı harici sistemde oluştur veya mevcut ise güncelle.
     *
     * @return array{success: bool, response: mixed, error: ?string}
     */
    public function syncUser(User $user): array;

    /**
     * Kullanıcı bilgilerini harici sistemde güncelle.
     *
     * @return array{success: bool, response: mixed, error: ?string}
     */
    public function updateUser(User $user): array;

    /**
     * Kullanıcıyı harici sistemden sil (soft-delete).
     */
    public function removeUser(User $user): bool;

    /**
     * Harici sistemden kullanıcı verisini getir (raporlama için).
     */
    public function getUser(User $user): ?array;

    /**
     * Connector entegrasyonu tamamlanmış mı?
     * false ise henüz API endpoint'leri hazır değil.
     */
    public static function isReady(): bool;
}
