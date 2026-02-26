<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Page::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|array',
            'title.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|array',
            'content.*' => 'nullable|string',
            'excerpt' => 'nullable|array',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'featured_image' => 'nullable|image|max:2048',
            'template' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer',
            'is_homepage' => 'boolean',
        ];
    }
}
