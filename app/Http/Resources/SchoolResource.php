<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for School model.
 */
class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'country'       => $this->country,
            'state'         => $this->state,
            'city'          => $this->city,
            'is_active'     => $this->is_active,
            'users_count'   => $this->whenCounted('users'),
            'classes_count' => $this->whenCounted('classes'),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
