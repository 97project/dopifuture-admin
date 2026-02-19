<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Applications", description="DopiFuture Application management")
 */
class ApplicationApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/applications",
     *     summary="List all active applications",
     *     tags={"Applications"},
     *     @OA\Parameter(name="Accept-Language", in="header", required=false, @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Success",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="icon", type="string"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();
        $apps = Application::active()->ordered()->get();

        return response()->json([
            'data' => $apps->map(fn($a) => [
                'id' => $a->id,
                'slug' => $a->slug,
                'name' => $a->getTranslation('name', $locale),
                'description' => $a->getTranslation('description', $locale),
                'icon' => $a->icon,
                'color' => $a->color,
                'is_active' => $a->is_active,
            ]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applications/{slug}",
     *     summary="Get a single application by slug",
     *     tags={"Applications"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $locale = app()->getLocale();
        $app = Application::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $app->id,
                'slug' => $app->slug,
                'name' => $app->getTranslation('name', $locale),
                'description' => $app->getTranslation('description', $locale),
                'icon' => $app->icon,
                'color' => $app->color,
                'is_active' => $app->is_active,
            ],
        ]);
    }
}
