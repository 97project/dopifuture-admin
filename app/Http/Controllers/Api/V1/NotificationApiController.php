<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Notifications", description="User notification management")
 */
class NotificationApiController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/notifications",
     *     operationId="notificationList",
     *     tags={"Notifications"},
     *     summary="List user notifications (paginated)",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated notifications"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate($request->input('per_page', 20));

        return $this->success($notifications);
    }

    /**
     * @OA\Get(
     *     path="/notifications/unread-count",
     *     operationId="notificationUnreadCount",
     *     tags={"Notifications"},
     *     summary="Get unread notification count",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="Unread count",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function unreadCount(Request $request)
    {
        return $this->success([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/notifications/{id}/read",
     *     operationId="notificationMarkRead",
     *     tags={"Notifications"},
     *     summary="Mark a notification as read",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Notification marked as read"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->success(['message' => __('api.notification_read')]);
    }

    /**
     * @OA\Post(
     *     path="/notifications/read-all",
     *     operationId="notificationMarkAllRead",
     *     tags={"Notifications"},
     *     summary="Mark all notifications as read",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Response(response=200, description="All notifications marked as read")
     * )
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(['message' => __('api.all_notifications_read')]);
    }

    /**
     * @OA\Delete(
     *     path="/notifications/{id}",
     *     operationId="notificationDestroy",
     *     tags={"Notifications"},
     *     summary="Delete a notification",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Notification deleted"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return $this->success(['message' => __('api.notification_deleted')]);
    }

    /**
     * @OA\Post(
     *     path="/auth/device-token",
     *     operationId="updateDeviceToken",
     *     tags={"Notifications"},
     *     summary="Register or update FCM device token",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"device_token","device_platform"},
     *             @OA\Property(property="device_token", type="string"),
     *             @OA\Property(property="device_platform", type="string", enum={"ios","android","web"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token updated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string|max:500',
            'device_platform' => 'required|in:ios,android,web',
        ]);

        $request->user()->update([
            'device_token' => $request->input('device_token'),
            'device_platform' => $request->input('device_platform'),
        ]);

        return $this->success(['message' => __('api.device_token_updated')]);
    }
}
