<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * List paginated notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getNotifications(
            $request->user(),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return response()->json(
            NotificationResource::collection($notifications)->response()->getData(true)
        );
    }

    /**
     * Get the unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->unreadCount($request->user());

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $marked = $this->notificationService->markAsRead($request->user(), $id);

        if (!$marked) {
            return response()->json(
                ['message' => 'Notification not found.'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'message' => "Marked {$count} notifications as read.",
            'marked_count' => $count,
        ]);
    }
}
