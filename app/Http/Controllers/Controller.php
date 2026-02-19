<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="Panel26 Admin API",
 *     version="1.0.0",
 *     description="Panel26 Admin Panel REST API - Bearer token and API Key authentication supported. All endpoints accept ?lang=tr|en query parameter for localized responses.",
 *     @OA\Contact(email="admin@panel26.com")
 * )
 *
 * @OA\Server(url="/api/v1", description="API V1 Server")
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token",
 *     description="Sanctum Bearer Token"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="apiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-KEY",
 *     description="API Key authentication"
 * )
 */
abstract class Controller
{
    use AuthorizesRequests;
}
