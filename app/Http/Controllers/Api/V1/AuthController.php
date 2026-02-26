<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\AuthService;
use App\Services\TwoFactorService;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Auth", description="Authentication endpoints")
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService,
        protected TwoFactorService $twoFactorService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Auth"},
     *     summary="Login with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="admin@panel26.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 15")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=429, description="Too many attempts")
     * )
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->attemptLogin(
            $request->input('email'),
            $request->input('password'),
            $request->ip()
        );

        if (!$result['success']) {
            return $this->error($result['error'], $result['message'], [], 401);
        }

        $user = $result['user'];

        if ($user->hasTwoFactorEnabled()) {
            return $this->success([
                'requires_2fa' => true,
                'temp_token' => encrypt($user->id . '|' . now()->timestamp),
            ]);
        }

        $deviceName = $request->input('device_name', 'api');
        $token = $this->authService->createApiToken($user, $deviceName);

        return $this->success([
            'user' => UserResource::make($user)->withPermissions(),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/2fa/verify",
     *     tags={"Auth"},
     *     summary="Verify 2FA code after login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"temp_token","code"},
     *             @OA\Property(property="temp_token", type="string"),
     *             @OA\Property(property="code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="2FA verified"),
     *     @OA\Response(response=401, description="Invalid code")
     * )
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'temp_token' => 'required|string',
            'code' => 'required|string',
        ]);

        try {
            $decrypted = decrypt($request->input('temp_token'));
            [$userId, $timestamp] = explode('|', $decrypted);

            if (now()->timestamp - (int) $timestamp > 300) {
                return $this->error('TOKEN_EXPIRED', __('auth.2fa_token_expired'), [], 401);
            }

            $user = \App\Models\User::findOrFail($userId);
        } catch (\Exception $e) {
            return $this->error('INVALID_TOKEN', __('auth.invalid_token'), [], 401);
        }

        $code = $request->input('code');
        if (
            !$this->twoFactorService->verifyCode($user, $code) &&
            !$this->twoFactorService->verifyRecoveryCode($user, $code)
        ) {
            return $this->error('INVALID_2FA_CODE', __('auth.2fa_code_invalid'), [], 401);
        }

        $token = $this->authService->createApiToken($user, $request->input('device_name', 'api'));

        return $this->success([
            'user' => UserResource::make($user)->withPermissions(),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout and revoke current token",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="Logged out")
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->success(null, ['message' => __('auth.logged_out')]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     tags={"Auth"},
     *     summary="Get current authenticated user",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="Current user data")
     * )
     */
    public function me(Request $request)
    {
        return $this->success(UserResource::make($request->user())->withPermissions());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/tokens",
     *     tags={"Auth"},
     *     summary="List user tokens",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="Token list")
     * )
     */
    public function tokens(Request $request)
    {
        $tokens = $request->user()->tokens()->latest()->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'abilities' => $t->abilities,
            'last_used_at' => $t->last_used_at?->toIso8601String(),
            'created_at' => $t->created_at->toIso8601String(),
        ]);

        return $this->success($tokens);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/tokens/{id}",
     *     tags={"Auth"},
     *     summary="Revoke a token",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Token revoked"),
     *     @OA\Response(response=404, description="Token not found")
     * )
     */
    public function revokeToken(Request $request, int $id)
    {
        if (!$this->authService->revokeApiToken($request->user(), $id)) {
            return $this->notFound(__('api.token_not_found'));
        }

        return $this->success(null, ['message' => __('api.token_revoked')]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/account",
     *     tags={"Auth"},
     *     summary="Delete current user account (GDPR compliant)",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Account deleted"),
     *     @OA\Response(response=401, description="Invalid password")
     * )
     */
    public function deleteAccount(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $service = new \App\Services\AccountDeletionService();
        if (!$service->confirmAndDelete($request->user(), $request->input('password'))) {
            return $this->error('INVALID_PASSWORD', __('auth.password_incorrect'), [], 401);
        }

        return $this->success(null, ['message' => __('api.user_deleted')]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/account/delete-request",
     *     tags={"Auth"},
     *     summary="Request account deletion (sends confirmation email)",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="Confirmation email sent"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function requestDeletion(Request $request)
    {
        $service = new \App\Services\AccountDeletionService();
        $service->requestDeletion($request->user());

        return $this->success(null, ['message' => __('api.deletion_request_sent')]);
    }

}
