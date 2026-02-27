<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Category::class);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|array',
            'name.*'      => 'string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|array',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'boolean',
            'type'        => 'required|in:post,page,faq',
        ];
    }
}
