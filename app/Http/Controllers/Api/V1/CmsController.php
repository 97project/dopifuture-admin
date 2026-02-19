<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Media;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Menu;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="CMS", description="Public CMS endpoints (pages, posts, categories, tags, menus)")
 */
class CmsController extends Controller
{
    use ApiResponse;

    // ── Pages ───────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/cms/pages",
     *     operationId="cmsListPages",
     *     tags={"CMS"},
     *     summary="List published pages",
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="template", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Paginated list of published pages",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function pages(Request $request)
    {
        $query = Page::published()->with('author:id,name');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('template')) {
            $query->byTemplate($request->input('template'));
        }

        $pages = $query->orderBy('sort_order')->paginate($request->input('per_page', 15));
        return $this->success($pages);
    }

    /**
     * @OA\Get(
     *     path="/cms/pages/{slug}",
     *     operationId="cmsShowPage",
     *     tags={"CMS"},
     *     summary="Get a single published page by slug",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Page detail"),
     *     @OA\Response(response=404, description="Page not found")
     * )
     */
    public function page(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->with('author:id,name')->firstOrFail();
        return $this->success($page);
    }

    // ── Posts ────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/cms/posts",
     *     operationId="cmsListPosts",
     *     tags={"CMS"},
     *     summary="List published posts",
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="Category slug", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tag", in="query", description="Tag slug", @OA\Schema(type="string")),
     *     @OA\Parameter(name="featured", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Paginated list of published posts")
     * )
     */
    public function posts(Request $request)
    {
        $query = Post::published()->with(['author:id,name', 'categories', 'tags']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->input('category')));
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $request->input('tag')));
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $posts = $query->latest('published_at')->paginate($request->input('per_page', 15));
        return $this->success($posts);
    }

    /**
     * @OA\Get(
     *     path="/cms/posts/{slug}",
     *     operationId="cmsShowPost",
     *     tags={"CMS"},
     *     summary="Get a single published post by slug (increments view count)",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Post detail with author, categories, tags"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function post(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['author:id,name', 'categories', 'tags'])->firstOrFail();
        $post->increment('view_count');
        return $this->success($post);
    }

    // ── Categories ──────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/cms/categories",
     *     operationId="cmsListCategories",
     *     tags={"CMS"},
     *     summary="List active categories with children",
     *     @OA\Parameter(name="type", in="query", description="Category type", @OA\Schema(type="string", enum={"post","page","faq"}, default="post")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Tree of active categories")
     * )
     */
    public function categories(Request $request)
    {
        $type = $request->input('type', 'post');
        $categories = Category::active()->byType($type)
            ->roots()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return $this->success($categories);
    }

    // ── Tags ────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/cms/tags",
     *     operationId="cmsListTags",
     *     tags={"CMS"},
     *     summary="List all tags",
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="List of all tags")
     * )
     */
    public function tags()
    {
        $tags = Tag::orderBy('name')->get();
        return $this->success($tags);
    }

    // ── Menus ───────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/cms/menus",
     *     operationId="cmsListMenus",
     *     tags={"CMS"},
     *     summary="List active menus with items",
     *     @OA\Parameter(name="location", in="query", description="Filter by location (header, footer, sidebar)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="List of active menus with nested items")
     * )
     */
    public function menus(Request $request)
    {
        $location = $request->input('location');
        $query = Menu::active()->with(['items.children']);

        if ($location) {
            $query->byLocation($location);
        }

        $menus = $query->get();
        return $this->success($menus);
    }
}
