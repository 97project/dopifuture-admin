<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $type = $request->input('type', 'post');
        $query = Category::byType($type)->roots()->with('children')->orderBy('sort_order');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->paginate(30)->withQueryString();

        $stats = [
            'total' => Category::byType($type)->count(),
            'active' => Category::byType($type)->active()->count(),
            'inactive' => Category::byType($type)->where('is_active', false)->count(),
        ];

        return view('admin.categories.index', compact('categories', 'type', 'stats'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Category::class);
        $type = $request->input('type', 'post');
        $parents = Category::byType($type)->roots()->orderBy('sort_order')->get();
        return view('admin.categories.create', compact('type', 'parents'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $data = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|array',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'type' => 'required|in:post,page,faq',
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index', ['type' => $data['type']])
            ->with('success', __('admin.category_created'));
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);
        $parents = Category::byType($category->type)->roots()
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|array',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index', ['type' => $category->type])
            ->with('success', __('admin.category_updated'));
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $type = $category->type;
        $category->delete();

        return redirect()->route('admin.categories.index', ['type' => $type])
            ->with('success', __('admin.category_deleted'));
    }
}
