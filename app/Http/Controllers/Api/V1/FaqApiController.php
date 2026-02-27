<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="FAQ", description="Public FAQ endpoints")
 */
class FaqApiController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/faqs",
     *     operationId="faqList",
     *     tags={"FAQ"},
     *     summary="List all active FAQ categories with their active FAQs",
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="FAQ categories with nested FAQ items",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="object"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="faqs", type="array", @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="question", type="object"),
     *                         @OA\Property(property="answer", type="object")
     *                     ))
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $categories = FaqCategory::active()
            ->with(['faqs' => fn($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return $this->success($categories);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/faqs/{category}",
     *     operationId="faqShowCategory",
     *     tags={"FAQ"},
     *     summary="Get a single FAQ category with its active FAQs",
     *     @OA\Parameter(name="category", in="path", required=true, description="FAQ category ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="FAQ category with FAQ items"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show(FaqCategory $category)
    {
        $category->load(['faqs' => fn($q) => $q->active()->orderBy('sort_order')]);
        return $this->success($category);
    }
}
