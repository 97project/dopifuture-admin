<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    public function rules(): array
    {
        $id = $this->route('category')->id;
        return [
            'name'        => 'required|array',
            'name.*'      => 'string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|array',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
        ];
    }
}
