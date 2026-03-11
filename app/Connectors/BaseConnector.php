<?php

namespace App\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

/**
 * Base class for external API connectors (MissionWay, WayStartup).
 * Provides shared HTTP helpers, health check, and error detection.
 *
 * x-api-key header authentication.
 */
abstract class BaseConnector
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    /** Override in subclass for log prefixes, e.g. 'MissionWay' */
    protected string $logPrefix = 'Connector';

    /** Override in subclass if health endpoint differs */
    protected string $healthEndpoint = '/health/simple';

    /**
     * Initialise from connector config key.
     */
    public function __construct(string $configKey)
    {
        $config = config("connectors.{$configKey}");
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->timeout = $config['timeout'] ?? 10;
    }

    /* ─── HTTP helpers ──────────────────────────────── */

    /**
     * Build standard auth headers.
     * NestJS ApiKeyGuard expects Authorization: Bearer <token>,
     * x-api-key is kept as fallback for backward compatibility.
     */
    protected function authHeaders(): array
    {
        return [
            'x-api-key'      => $this->apiKey,
            'Authorization'  => 'Bearer ' . $this->apiKey,
            'Accept'         => 'application/json',
        ];
    }

    /**
     * Generic authenticated GET request with error logging.
     */
    protected function apiGet(string $path, array $params = []): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->authHeaders())
                ->get("{$this->baseUrl}{$path}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::channel('daily')->warning("[{$this->logPrefix}] GET yanıt hatası", [
                'path' => $path,
                'status' => $response->status(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error("[{$this->logPrefix}] API GET hatası", [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Authenticated POST request.
     */
    protected function apiPost(string $path, array $data = []): Response
    {
        return Http::timeout($this->timeout)
            ->withHeaders($this->authHeaders())
            ->post("{$this->baseUrl}{$path}", $data);
    }

    /**
     * Authenticated DELETE request.
     */
    protected function apiDelete(string $path): Response
    {
        return Http::timeout($this->timeout)
            ->withHeaders($this->authHeaders())
            ->delete("{$this->baseUrl}{$path}");
    }

    /* ─── Health check ──────────────────────────────── */

    public function getHealthCheck(): ?array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}{$this->healthEndpoint}");

            return [
                'status' => $response->successful() ? 'ok' : 'error',
                'http_code' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unreachable',
                'http_code' => null,
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /* ─── Error detection ────────────────────────── */

    protected function isDuplicateError(Response $response): bool
    {
        $body = $response->json('message', '');
        return is_string($body) && str_contains(strtolower($body), 'already exists');
    }

    /* ─── Static ─────────────────────────────────── */

    public static function isReady(): bool
    {
        return true;
    }
}
