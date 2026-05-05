<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Get paginated notifications for a user.
     */
    public function getNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();
        return $count;
    }
}
