<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DualAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Try Bearer token first (Sanctum)
        if ($request->bearerToken()) {
            $guard = auth('sanctum');
            if ($guard->check()) {
                auth()->login($guard->user());
                return $next($request);
            }
        }

        // Try API Key
        $apiKeyValue = $request->header('X-API-KEY');
        if ($apiKeyValue) {
            $keyHash = hash('sha256', $apiKeyValue);
            $apiKey = \App\Models\ApiKey::where('key_hash', $keyHash)->first();

            if ($apiKey && $apiKey->isValid() && $apiKey->isIpAllowed($request->ip())) {
                $apiKey->update([
                    'last_used_at' => now(),
                    'last_used_ip' => $request->ip(),
                ]);
                auth()->login($apiKey->user);
                $request->attributes->set('api_key', $apiKey);
                return $next($request);
            }
        }

        return response()->json([
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => __('api.unauthorized'),
            ],
            'meta' => ['locale' => app()->getLocale()],
        ], 401);
    }
}
