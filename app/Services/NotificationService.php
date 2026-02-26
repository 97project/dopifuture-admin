<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to a user using a template key.
     */
    public function send(User $user, string $templateKey, array $data = []): bool
    {
        $template = NotificationTemplate::findByKey($templateKey);
        if (!$template) {
            Log::warning("Notification template not found: {$templateKey}");
            return false;
        }

        $locale = $user->locale ?? app()->getLocale();
        $title = $this->interpolate($template->getTranslation('title', $locale), $data);
        $body = $this->interpolate($template->getTranslation('body', $locale), $data);
        $channels = $template->channels ?? ['database'];

        if (in_array('database', $channels)) {
            $this->sendDatabase($user, $title, $body, $data);
        }

        if (in_array('fcm', $channels) && $user->device_token) {
            $this->sendFcm($user, $title, $body, $data);
        }

        return true;
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers(array $userIds, string $templateKey, array $data = []): int
    {
        $count = 0;
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            if ($this->send($user, $templateKey, $data)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Send to all active users.
     */
    public function sendToAll(string $templateKey, array $data = []): int
    {
        $count = 0;
        User::where('status', 'active')->chunk(100, function ($users) use ($templateKey, $data, &$count) {
            foreach ($users as $user) {
                if ($this->send($user, $templateKey, $data)) {
                    $count++;
                }
            }
        });
        return $count;
    }

    /**
     * Send a custom notification (no template).
     */
    public function sendCustom(User $user, string $title, string $body, array $data = [], bool $pushFcm = true): void
    {
        $this->sendDatabase($user, $title, $body, $data);

        if ($pushFcm && $user->device_token) {
            $this->sendFcm($user, $title, $body, $data);
        }
    }

    /**
     * Store notification in database via Laravel's notification system.
     */
    protected function sendDatabase(User $user, string $title, string $body, array $data = []): void
    {
        $user->notify(
            (new GeneralNotification($title, $body, $data))->onQueue('notifications')
        );
    }

    /**
     * Send FCM push notification via HTTP v1 API.
     *
     * @see https://firebase.google.com/docs/cloud-messaging/migrate-v1
     */
    protected function sendFcm(User $user, string $title, string $body, array $data = []): bool
    {
        $projectId = config('services.fcm.project_id');
        if (!$projectId) {
            Log::warning('FCM project_id not configured');
            return false;
        }

        $accessToken = $this->getFcmAccessToken();
        if (!$accessToken) {
            return false;
        }

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $message = [
                'message' => [
                    'token' => $user->device_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => collect($data)->map(fn($v) => (string) $v)->toArray(),
                ],
            ];

            // Platform-specific config
            if ($user->device_platform === 'android') {
                $message['message']['android'] = [
                    'priority' => 'high',
                    'notification' => ['sound' => 'default'],
                ];
            } elseif ($user->device_platform === 'ios') {
                $message['message']['apns'] = [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => $user->unreadNotifications()->count(),
                        ],
                    ],
                ];
            }

            $response = Http::withToken($accessToken)
                ->post($url, $message);

            if (!$response->successful()) {
                Log::error('FCM v1 send failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'error' => $response->json('error.message', $response->body()),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get OAuth2 access token for FCM HTTP v1 API.
     * Uses service account credentials JSON file.
     */
    protected function getFcmAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            $credentialsPath = config('services.fcm.credentials');
            if (!$credentialsPath || !file_exists($credentialsPath)) {
                Log::warning('FCM credentials file not found', ['path' => $credentialsPath]);
                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            if (!$credentials) {
                Log::error('FCM credentials file is invalid JSON');
                return null;
            }

            try {
                // Create JWT for Google OAuth2
                $now = time();
                $header = self::base64urlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
                $payload = self::base64urlEncode(json_encode([
                    'iss' => $credentials['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                ]));

                $signatureInput = "{$header}.{$payload}";
                openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256');
                $jwt = "{$signatureInput}." . self::base64urlEncode($signature);

                // Exchange JWT for access token
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if (!$response->successful()) {
                    Log::error('FCM OAuth2 token exchange failed', [
                        'status' => $response->status(),
                        'error' => $response->body(),
                    ]);
                    return null;
                }

                return $response->json('access_token');
            } catch (\Exception $e) {
                Log::error('FCM OAuth2 exception', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Replace {placeholder} in template strings.
     */
    protected function interpolate(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $text = str_replace('{' . $key . '}', $value, $text);
            }
        }
        return $text;
    }

    /**
     * URL-safe base64 encode helper.
     */
    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
