<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ApiKeyController;
use App\Http\Controllers\Api\V1\GeneralController;
use App\Http\Controllers\Api\V1\CmsController;
use App\Http\Controllers\Api\V1\MediaApiController;
use App\Http\Controllers\Api\V1\FaqApiController;
use App\Http\Controllers\Api\V1\FormApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;

use App\Http\Controllers\Api\V1\ApplicationApiController;
use App\Http\Controllers\Api\V1\SchoolApiController;
use App\Http\Controllers\Api\V1\LicenseApiController;
use App\Http\Controllers\Api\V1\RegistrationApiController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware([\App\Http\Middleware\SetLocale::class])->group(function () {

    // Public auth routes
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');
    Route::post('auth/2fa/verify', [AuthController::class, 'verify2fa'])
        ->middleware('throttle:5,1');

    // Public endpoints
    Route::get('languages', [GeneralController::class, 'languages']);
    Route::get('settings/public', [GeneralController::class, 'publicSettings']);

    // Public CMS endpoints
    Route::get('pages', [CmsController::class, 'pages']);
    Route::get('pages/{slug}', [CmsController::class, 'page']);
    Route::get('posts', [CmsController::class, 'posts']);
    Route::get('posts/{slug}', [CmsController::class, 'post']);
    Route::get('categories', [CmsController::class, 'categories']);
    Route::get('tags', [CmsController::class, 'tags']);
    Route::get('menus', [CmsController::class, 'menus']);

    // Public FAQ
    Route::get('faqs', [FaqApiController::class, 'index']);
    Route::get('faqs/{category}', [FaqApiController::class, 'show']);

    // Public Forms
    Route::get('forms/{slug}', [FormApiController::class, 'show']);
    Route::post('forms/{slug}/submit', [FormApiController::class, 'submit'])
        ->middleware('throttle:10,1');

    // DopiFuture — Public endpoints
    Route::get('applications', [ApplicationApiController::class, 'index']);
    Route::get('applications/{slug}', [ApplicationApiController::class, 'show']);
    Route::post('register', [RegistrationApiController::class, 'store'])
        ->middleware('throttle:5,1');

    // Authenticated routes (Dual Auth: Bearer or API Key)
    Route::middleware([\App\Http\Middleware\DualAuth::class])->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::get('auth/tokens', [AuthController::class, 'tokens']);
        Route::delete('auth/tokens/{id}', [AuthController::class, 'revokeToken']);

        // Users
        Route::apiResource('users', UserController::class);

        // API Keys
        Route::get('api-keys', [ApiKeyController::class, 'index']);
        Route::post('api-keys', [ApiKeyController::class, 'store']);
        Route::post('api-keys/{apiKey}/rotate', [ApiKeyController::class, 'rotate']);
        Route::post('api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke']);
        Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy']);

        // Roles
        Route::get('roles', [GeneralController::class, 'roles']);

        // Activity Logs
        Route::get('activity-logs', [GeneralController::class, 'activityLogs']);

        // Media (authenticated)
        Route::get('media', [MediaApiController::class, 'index']);
        Route::post('media', [MediaApiController::class, 'store']);
        Route::delete('media/{media}', [MediaApiController::class, 'destroy']);

        // Notifications (authenticated)
        Route::get('notifications', [NotificationApiController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
        Route::post('notifications/{id}/read', [NotificationApiController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationApiController::class, 'markAllRead']);
        Route::delete('notifications/{id}', [NotificationApiController::class, 'destroy']);
        Route::post('auth/device-token', [NotificationApiController::class, 'updateDeviceToken']);

        // Account Deletion
        Route::delete('auth/account', [AuthController::class, 'deleteAccount']);
        Route::post('auth/account/delete-request', [AuthController::class, 'requestDeletion']);

        // DopiFuture — Authenticated endpoints
        Route::get('schools', [SchoolApiController::class, 'index']);
        Route::get('schools/{school}', [SchoolApiController::class, 'show']);
        Route::get('licenses', [LicenseApiController::class, 'index']);
    });
});
