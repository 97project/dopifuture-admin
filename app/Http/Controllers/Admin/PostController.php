<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Post::class);

        $query = Post::with(['author', 'categories', 'tags']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->input('category')));
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['status', 'created_at', 'published_at', 'view_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = Category::byType('post')->active()->orderBy('sort_order')->get();

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
            'featured' => Post::where('is_featured', true)->count(),
        ];

        return view('admin.posts.index', compact('posts', 'categories', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', Post::class);
        $categories = Category::byType('post')->active()->orderBy('sort_order')->get();
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(PostStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $data['author_id'] = auth()->id();

        $post = Post::create(collect($data)->except(['categories', 'tags'])->toArray());

        if ($request->filled('categories')) {
            $post->categories()->sync($request->input('categories'));
        }

        $this->syncTags($post, $request->input('tags', []));

        return redirect()->route('admin.posts.index')
            ->with('success', __('admin.post_created'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        $post->load('categories', 'tags');
        $categories = Category::byType('post')->active()->orderBy('sort_order')->get();
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(PostUpdateRequest $request, Post $post)
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post->update(collect($data)->except(['categories', 'tags'])->toArray());
        $post->categories()->sync($request->input('categories', []));
        $this->syncTags($post, $request->input('tags', []));

        return redirect()->route('admin.posts.index')
            ->with('success', __('admin.post_updated'));
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', __('admin.post_deleted'));
    }

    protected function syncTags(Post $post, array $tagNames): void
    {
        $tagIds = [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name))
                continue;
            $tag = Tag::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => [app()->getLocale() => $name]]
            );
            $tagIds[] = $tag->id;
        }
        $post->tags()->sync($tagIds);
    }
}
