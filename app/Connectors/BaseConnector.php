<?php

namespace App\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Base class for external API connectors.
 * Provides shared HTTP request helpers, health check, and error detection.
 */
abstract class BaseConnector
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    /**
     * Initialise from connector config.
     */
    public function __construct(string $configKey)
    {
        $config = config("connectors.{$configKey}");
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->timeout = $config['timeout'] ?? 10;
    }

    /* ─── HTTP helpers ──────────────────────────────── */

    protected function request(string $method, string $path, array $data = []): Response
    {
        $url = $this->baseUrl . $path;

        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->timeout($this->timeout);

        return match (strtoupper($method)) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'PATCH' => $request->patch($url, $data),
            'DELETE' => $request->delete($url, $data),
            default => $request->get($url, $data),
        };
    }

    protected function apiGet(string $path, array $query = []): ?array
    {
        try {
            $response = $this->request('GET', $path, $query);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::channel('daily')->error(static::class . ' GET ' . $path, [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /* ─── Health check ──────────────────────────────── */

    public function getHealthCheck(): array
    {
        try {
            $start = microtime(true);
            $response = $this->request('GET', '/api/v1/health');
            $elapsed = round((microtime(true) - $start) * 1000);

            return [
                'status' => $response->successful() ? 'ok' : 'error',
                'code' => $response->status(),
                'latency_ms' => $elapsed,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unreachable',
                'code' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /* ─── Error detection ────────────────────────── */

    protected function isDuplicateError(Response $response): bool
    {
        if ($response->status() === 409) {
            return true;
        }

        $body = $response->json();
        $message = $body['message'] ?? $body['error'] ?? '';

        if (is_string($message)) {
            return str_contains(strtolower($message), 'already exists')
                || str_contains(strtolower($message), 'duplicate');
        }

        return false;
    }
}
