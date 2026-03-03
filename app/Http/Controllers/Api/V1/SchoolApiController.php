<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Schools", description="DopiFuture School management")
 */
class SchoolApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/schools",
     *     summary="List active schools",
     *     tags={"Schools"},
     *     security={{"bearerAuth":{}},{"apiKey":{}}},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        $schools = School::active()
            ->withCount(['classes', 'users'])
            ->get();

        return response()->json([
            'data' => $schools->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'country' => $s->country,
                'state' => $s->state,
                'city' => $s->city,
                'phone' => $s->phone,
                'email' => $s->email,
                'website' => $s->website,
                'is_active' => $s->is_active,
                'classes_count' => $s->classes_count,
                'users_count' => $s->users_count,
            ]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/schools/{id}",
     *     summary="Get a school with its classes",
     *     tags={"Schools"},
     *     security={{"bearerAuth":{}},{"apiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(School $school): JsonResponse
    {
        $school->load(['classes' => fn($q) => $q->active(), 'licenses' => fn($q) => $q->active()]);

        return response()->json([
            'data' => [
                'id' => $school->id,
                'name' => $school->name,
                'country' => $school->country,
                'state' => $school->state,
                'city' => $school->city,
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email,
                'website' => $school->website,
                'is_active' => $school->is_active,
                'classes' => $school->classes->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade_level' => $c->grade_level,
                    'academic_year' => $c->academic_year,
                ]),
                'licenses_count' => $school->licenses->count(),
            ],
        ]);
    }
}
