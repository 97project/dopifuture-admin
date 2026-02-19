<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*') || $request->is('admin')) {
                return route('admin.login');
            }
            return '/login';
        });

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'dual.auth' => \App\Http\Middleware\DualAuth::class,
            'api.key' => \App\Http\Middleware\AuthenticateApiKey::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'secure.headers' => \App\Http\Middleware\SecureHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => __('api.validation_error'),
                        'details' => ['fields' => $e->errors()],
                    ],
                    'meta' => ['locale' => app()->getLocale()],
                ], 422);
            }
        });

        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => __('api.unauthorized'),
                    ],
                    'meta' => ['locale' => app()->getLocale()],
                ], 401);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => __('api.not_found'),
                    ],
                    'meta' => ['locale' => app()->getLocale()],
                ], 404);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'TOO_MANY_REQUESTS',
                        'message' => __('api.too_many_requests'),
                    ],
                    'meta' => ['locale' => app()->getLocale()],
                ], 429);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => __('api.forbidden'),
                    ],
                    'meta' => ['locale' => app()->getLocale()],
                ], 403);
            }
        });
    })->create();
