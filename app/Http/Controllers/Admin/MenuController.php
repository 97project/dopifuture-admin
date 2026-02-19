<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Menu::class);
        $menus = Menu::withCount('allItems')->orderBy('name')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $this->authorize('create', Menu::class);
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Menu::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|in:header,footer,sidebar',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($data);

        return redirect()->route('admin.menus.edit', $menu)
            ->with('success', __('admin.menu_created'));
    }

    public function edit(Menu $menu)
    {
        $this->authorize('update', $menu);
        $menu->load(['allItems' => fn($q) => $q->orderBy('sort_order')]);

        $pages = Page::published()->get(['id', 'title', 'slug']);
        $posts = Post::published()->get(['id', 'title', 'slug']);
        $categories = Category::active()->get(['id', 'name', 'slug']);

        return view('admin.menus.edit', compact('menu', 'pages', 'posts', 'categories'));
    }

    public function update(Request $request, Menu $menu)
    {
        $this->authorize('update', $menu);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|in:header,footer,sidebar',
            'is_active' => 'boolean',
        ]);

        $menu->update($data);

        return redirect()->route('admin.menus.index')
            ->with('success', __('admin.menu_updated'));
    }

    public function destroy(Menu $menu)
    {
        $this->authorize('delete', $menu);
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', __('admin.menu_deleted'));
    }

    // ── Menu Item Management ────────────────────────────────

    public function storeItem(Request $request, Menu $menu)
    {
        $this->authorize('update', $menu);

        $data = $request->validate([
            'title' => 'required|array',
            'title.*' => 'string|max:255',
            'url' => 'nullable|string|max:500',
            'linkable_type' => 'nullable|string|in:page,post,category',
            'linkable_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer|exists:menu_items,id',
            'target' => 'nullable|in:_self,_blank',
            'icon' => 'nullable|string|max:100',
        ]);

        $maxOrder = $menu->allItems()->max('sort_order') ?? 0;

        // Resolve morph type
        if (!empty($data['linkable_type']) && !empty($data['linkable_id'])) {
            $morphMap = ['page' => Page::class, 'post' => Post::class, 'category' => Category::class];
            $data['linkable_type'] = $morphMap[$data['linkable_type']] ?? null;
        }

        $data['menu_id'] = $menu->id;
        $data['sort_order'] = $maxOrder + 1;

        MenuItem::create($data);

        return back()->with('success', __('admin.menu_item_added'));
    }

    public function updateItems(Request $request, Menu $menu)
    {
        $this->authorize('update', $menu);

        $items = $request->input('items', []);
        foreach ($items as $index => $itemData) {
            MenuItem::where('id', $itemData['id'])->where('menu_id', $menu->id)->update([
                'sort_order' => $index,
                'parent_id' => $itemData['parent_id'] ?? null,
            ]);
        }

        return back()->with('success', __('admin.menu_order_updated'));
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        $this->authorize('update', $menu);

        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        $item->delete();

        return back()->with('success', __('admin.menu_item_deleted'));
    }
}
