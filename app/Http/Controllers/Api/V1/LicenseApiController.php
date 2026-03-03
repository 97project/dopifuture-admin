<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Licenses", description="DopiFuture License management")
 */
class LicenseApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/licenses",
     *     summary="List licenses for the authenticated user's schools",
     *     tags={"Licenses"},
     *     security={{"bearerAuth":{}},{"apiKey":{}}},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        // Get school IDs this user belongs to
        $schoolIds = $user->schools()->pluck('schools.id');

        $licenses = License::whereIn('school_id', $schoolIds)
            ->with('school')
            ->latest()
            ->get();

        return response()->json([
            'data' => $licenses->map(fn($l) => [
                'id' => $l->id,
                'school' => $l->school?->name,
                'seat_count' => $l->totalSeats(),
                'used_seats' => $l->used_seats,
                'available_seats' => $l->availableSeats(),
                'starts_at' => $l->starts_at?->toDateString(),
                'expires_at' => $l->expires_at?->toDateString(),
                'is_active' => $l->is_active,
                'is_expired' => $l->isExpired(),
            ]),
        ]);
    }
}
