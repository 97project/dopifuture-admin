<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Faq::class);

        $categories = FaqCategory::with(['faqs' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total_categories' => FaqCategory::count(),
            'total_faqs' => Faq::count(),
            'active_faqs' => Faq::where('is_active', true)->count(),
        ];

        return view('admin.faqs.index', compact('categories', 'stats'));
    }

    // ── FAQ Category ────────────────────────────────────────

    public function createCategory()
    {
        $this->authorize('create', Faq::class);
        return view('admin.faqs.create-category');
    }

    public function storeCategory(Request $request)
    {
        $this->authorize('create', Faq::class);

        $data = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_categories,slug',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        FaqCategory::create($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_category_created'));
    }

    public function editCategory(FaqCategory $category)
    {
        $this->authorize('update', new Faq);
        return view('admin.faqs.edit-category', compact('category'));
    }

    public function updateCategory(Request $request, FaqCategory $category)
    {
        $this->authorize('update', new Faq);

        $data = $request->validate([
            'name' => 'required|array',
            'name.*' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_categories,slug,' . $category->id,
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_category_updated'));
    }

    public function destroyCategory(FaqCategory $category)
    {
        $this->authorize('delete', new Faq);
        $category->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_category_deleted'));
    }

    // ── FAQ Items ───────────────────────────────────────────

    public function create(Request $request)
    {
        $this->authorize('create', Faq::class);
        $categories = FaqCategory::active()->orderBy('sort_order')->get();
        $selectedCategory = $request->input('category_id');
        return view('admin.faqs.create', compact('categories', 'selectedCategory'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Faq::class);

        $data = $request->validate([
            'faq_category_id' => 'required|integer|exists:faq_categories,id',
            'question' => 'required|array',
            'question.*' => 'string|max:500',
            'answer' => 'required|array',
            'answer.*' => 'string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Faq::create($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_created'));
    }

    public function edit(Faq $faq)
    {
        $this->authorize('update', $faq);
        $categories = FaqCategory::active()->orderBy('sort_order')->get();
        return view('admin.faqs.edit', compact('faq', 'categories'));
    }

    public function update(Request $request, Faq $faq)
    {
        $this->authorize('update', $faq);

        $data = $request->validate([
            'faq_category_id' => 'required|integer|exists:faq_categories,id',
            'question' => 'required|array',
            'question.*' => 'string|max:500',
            'answer' => 'required|array',
            'answer.*' => 'string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq->update($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_updated'));
    }

    public function destroy(Faq $faq)
    {
        $this->authorize('delete', $faq);
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', __('admin.faq_deleted'));
    }
}
