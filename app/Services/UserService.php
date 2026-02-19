<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\AvatarService;
use Illuminate\Support\Arr;

class UserService
{
    public function __construct(
        protected AvatarService $avatarService
    ) {
    }

    /**
     * List users with filters, sorting, and pagination.
     */
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = User::with('roles');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['direction'] ?? 'desc';
        $allowedSorts = ['name', 'email', 'status', 'created_at', 'last_login_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        $userData = Arr::except($data, ['roles', 'avatar', 'password_confirmation']);
        $user = User::create($userData);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['avatar'])) {
            $this->avatarService->upload($user, $data['avatar']);
        }

        ActivityLog::log('created', 'users', $user, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return $user->fresh('roles');
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $userData = Arr::except($data, ['roles', 'avatar', 'password_confirmation']);
        $user->update($userData);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        if (!empty($data['avatar'])) {
            $this->avatarService->upload($user, $data['avatar']);
        }

        ActivityLog::log('updated', 'users', $user, [
            'name' => $user->name,
        ]);

        return $user->fresh('roles');
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        ActivityLog::log('deleted', 'users', $user, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return $user->delete();
    }

    /**
     * Perform bulk action on users.
     */
    public function bulkAction(string $action, array $ids, int $excludeUserId): int
    {
        $ids = array_diff($ids, [$excludeUserId]);

        if (empty($ids)) {
            return 0;
        }

        switch ($action) {
            case 'delete':
                User::whereIn('id', $ids)->delete();
                break;
            case 'activate':
                User::whereIn('id', $ids)->update(['status' => 'active']);
                break;
            case 'deactivate':
                User::whereIn('id', $ids)->update(['status' => 'inactive']);
                break;
        }

        ActivityLog::log('bulk_' . $action, 'users', null, [
            'count' => count($ids),
            'ids' => $ids,
        ]);

        return count($ids);
    }
}
