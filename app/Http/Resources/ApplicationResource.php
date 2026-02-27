<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Application model.
 */
class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->getTranslation('name', app()->getLocale()),
            'slug'         => $this->slug,
            'description'  => $this->getTranslation('description', app()->getLocale()),
            'icon'         => $this->icon,
            'color'        => $this->color,
            'connector'    => $this->connector_type,
            'is_active'    => $this->is_active,
            'sort_order'   => $this->sort_order,
            'users_count'  => $this->whenCounted('users'),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
