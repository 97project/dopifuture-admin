<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Unified User API Resource.
 * Replaces duplicate formatUser() methods in AuthController and UserController.
 *
 * Usage:
 *   return new UserResource($user);                     // basic
 *   return UserResource::make($user)->withPermissions(); // with permissions
 */
class UserResource extends JsonResource
{
    private bool $includePermissions = false;

    /**
     * Include permission list in the response.
     */
    public function withPermissions(): static
    {
        $this->includePermissions = true;
        return $this;
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'dark_mode' => $this->dark_mode,
            'has_2fa' => $this->hasTwoFactorEnabled(),
            'avatar_url' => $this->avatar_url,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->when($this->includePermissions, fn() => $this->getAllPermissions()->pluck('name')),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ], fn($v) => $v !== null);
    }
}
