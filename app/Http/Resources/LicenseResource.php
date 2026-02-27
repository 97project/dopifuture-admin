<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for License model.
 */
class LicenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'school_id'       => $this->school_id,
            'school'          => new SchoolResource($this->whenLoaded('school')),
            'seat_count'      => $this->seat_count,
            'used_seats'      => $this->used_seats,
            'available_seats' => max(0, $this->seat_count - $this->used_seats),
            'starts_at'       => $this->starts_at?->toIso8601String(),
            'expires_at'      => $this->expires_at?->toIso8601String(),
            'is_active'       => $this->is_active,
            'is_expired'      => $this->expires_at && $this->expires_at->isPast(),
            'notes'           => $this->notes,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
