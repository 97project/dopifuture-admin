<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\ApiKeyService;
use App\Http\Requests\ApiKeyStoreRequest;
use App\Models\ApiKey;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="API Keys", description="API Key management")
 */
class ApiKeyController extends Controller
{
    use ApiResponse;

    public function __construct(protected ApiKeyService $apiKeyService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/api-keys",
     *     tags={"API Keys"},
     *     summary="List user's API keys",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="API key list")
     * )
     */
    public function index(Request $request)
    {
        $keys = $request->user()->apiKeys()
            ->latest()
            ->get()
            ->map(fn($k) => [
                'id' => $k->id,
                'name' => $k->name,
                'key_prefix' => $k->key_prefix . '...',
                'abilities' => $k->abilities,
                'ip_restrictions' => $k->ip_restrictions,
                'is_active' => $k->is_active,
                'expires_at' => $k->expires_at?->toIso8601String(),
                'last_used_at' => $k->last_used_at?->toIso8601String(),
                'created_at' => $k->created_at->toIso8601String(),
            ]);

        return $this->success($keys);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/api-keys",
     *     tags={"API Keys"},
     *     summary="Create a new API key",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="abilities", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="ip_restrictions", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="expires_at", type="string", format="date-time")
     *     )),
     *     @OA\Response(response=201, description="API key created (plain key shown once)")
     * )
     */
    public function store(ApiKeyStoreRequest $request)
    {
        $result = $this->apiKeyService->generate(
            $request->user()->id,
            $request->input('name'),
            $request->input('abilities', ['*']),
            $request->only('ip_restrictions', 'expires_at')
        );

        return $this->created([
            'id' => $result['api_key']->id,
            'name' => $result['api_key']->name,
            'key' => $result['plain_key'],
            'message' => __('api.api_key_created_warning'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/api-keys/{id}/rotate",
     *     tags={"API Keys"},
     *     summary="Rotate an API key",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Key rotated (new key shown once)")
     * )
     */
    public function rotate(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $result = $this->apiKeyService->rotate($apiKey);

        return $this->success([
            'id' => $result['api_key']->id,
            'key' => $result['plain_key'],
            'message' => __('api.api_key_rotated_warning'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/api-keys/{id}/revoke",
     *     tags={"API Keys"},
     *     summary="Revoke an API key",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Key revoked")
     * )
     */
    public function revoke(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $this->apiKeyService->revoke($apiKey);

        return $this->success(null, ['message' => __('api.api_key_revoked')]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/api-keys/{id}",
     *     tags={"API Keys"},
     *     summary="Delete an API key",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Key deleted")
     * )
     */
    public function destroy(Request $request, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $this->apiKeyService->delete($apiKey);

        return $this->success(null, ['message' => __('api.api_key_deleted')]);
    }
}
