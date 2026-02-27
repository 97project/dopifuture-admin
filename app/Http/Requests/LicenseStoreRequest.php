<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\License::class);
    }

    public function rules(): array
    {
        return [
            'school_id'  => 'required|exists:schools,id|unique:licenses,school_id',
            'seat_count' => 'required|integer|min:1',
            'starts_at'  => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'notes'      => 'nullable|string|max:1000',
            'is_active'  => 'nullable|boolean',
        ];
    }
}
