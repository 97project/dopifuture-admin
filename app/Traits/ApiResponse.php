<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, array $meta = [], int $status = 200): JsonResponse
    {
        $response = [];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        $response['meta'] = array_merge([
            'locale' => app()->getLocale(),
            'timestamp' => now()->toIso8601String(),
        ], $meta);

        return response()->json($response, $status);
    }

    protected function created($data = null, array $meta = []): JsonResponse
    {
        return $this->success($data, $meta, 201);
    }

    protected function error(string $code, string $message, array $details = [], int $status = 400): JsonResponse
    {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'meta' => [
                'locale' => app()->getLocale(),
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $status);
    }

    protected function validationError(array $errors): JsonResponse
    {
        return $this->error(
            'VALIDATION_ERROR',
            __('api.validation_error'),
            ['fields' => $errors],
            422
        );
    }

    protected function notFound(string $message = ''): JsonResponse
    {
        return $this->error(
            'NOT_FOUND',
            $message ?: __('api.not_found'),
            [],
            404
        );
    }

    protected function unauthorized(string $message = ''): JsonResponse
    {
        return $this->error(
            'UNAUTHORIZED',
            $message ?: __('api.unauthorized'),
            [],
            401
        );
    }

    protected function forbidden(string $message = ''): JsonResponse
    {
        return $this->error(
            'FORBIDDEN',
            $message ?: __('api.forbidden'),
            [],
            403
        );
    }

    protected function tooManyRequests(string $message = ''): JsonResponse
    {
        return $this->error(
            'TOO_MANY_REQUESTS',
            $message ?: __('api.too_many_requests'),
            [],
            429
        );
    }

    protected function paginated($paginator, array $meta = []): JsonResponse
    {
        return $this->success($paginator->items(), array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], $meta));
    }
}
