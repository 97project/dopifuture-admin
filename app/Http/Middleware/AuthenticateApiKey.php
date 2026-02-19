<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        $apiKeyValue = $request->header('X-API-KEY');

        if (!$apiKeyValue) {
            return response()->json([
                'error' => [
                    'code' => 'API_KEY_MISSING',
                    'message' => __('api.api_key_missing'),
                ],
                'meta' => ['locale' => app()->getLocale()],
            ], 401);
        }

        $keyHash = hash('sha256', $apiKeyValue);
        $apiKey = ApiKey::where('key_hash', $keyHash)->first();

        if (!$apiKey || !$apiKey->isValid()) {
            return response()->json([
                'error' => [
                    'code' => 'API_KEY_INVALID',
                    'message' => __('api.api_key_invalid'),
                ],
                'meta' => ['locale' => app()->getLocale()],
            ], 401);
        }

        if (!$apiKey->isIpAllowed($request->ip())) {
            return response()->json([
                'error' => [
                    'code' => 'IP_NOT_ALLOWED',
                    'message' => __('api.ip_not_allowed'),
                ],
                'meta' => ['locale' => app()->getLocale()],
            ], 403);
        }

        if ($ability && !$apiKey->hasAbility($ability)) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_ABILITY',
                    'message' => __('api.insufficient_ability'),
                ],
                'meta' => ['locale' => app()->getLocale()],
            ], 403);
        }

        $apiKey->update([
            'last_used_at' => now(),
            'last_used_ip' => $request->ip(),
        ]);

        auth()->login($apiKey->user);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
