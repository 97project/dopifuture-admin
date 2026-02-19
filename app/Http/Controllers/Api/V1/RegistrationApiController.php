<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Registration", description="Public school registration")
 */
class RegistrationApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Submit a school registration request",
     *     tags={"Registration"},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"school_name","contact_name","contact_surname","email"},
     *             @OA\Property(property="school_name", type="string", maxLength=200),
     *             @OA\Property(property="country", type="string", maxLength=100),
     *             @OA\Property(property="contact_name", type="string", maxLength=100),
     *             @OA\Property(property="contact_surname", type="string", maxLength=100),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string", maxLength=30),
     *             @OA\Property(property="notes", type="string", maxLength=2000)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration request created",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:200',
            'country' => 'nullable|string|max:100',
            'contact_name' => 'required|string|max:100',
            'contact_surname' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:2000',
        ]);

        $reg = RegistrationRequest::create(array_merge($validated, ['status' => 'new']));

        return response()->json([
            'message' => __('admin.request_submitted'),
            'id' => $reg->id,
        ], 201);
    }
}
