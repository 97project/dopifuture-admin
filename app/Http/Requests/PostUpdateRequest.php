<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    public function rules(): array
    {
        $postId = $this->route('post')->id;

        return [
            'title' => 'required|array',
            'title.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $postId,
            'content' => 'nullable|array',
            'content.*' => 'nullable|string',
            'excerpt' => 'nullable|array',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];
    }
}
