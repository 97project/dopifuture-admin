<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Page::class);

        $query = Page::with('author');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('template')) {
            $query->byTemplate($request->input('template'));
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['sort_order', 'status', 'created_at', 'published_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $pages = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'archived' => Page::where('status', 'archived')->count(),
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', Page::class);
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Page::class);

        $data = $request->validate([
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
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $data['author_id'] = auth()->id();

        Page::create($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin.page_created'));
    }

    public function edit(Page $page)
    {
        $this->authorize('update', $page);
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $data = $request->validate([
            'title' => 'required|array',
            'title.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
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
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin.page_updated'));
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin.page_deleted'));
    }
}
