<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Queue\SerializesModels;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Comment $comment,
        public readonly User $commenter,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') &&
            !$notifiable->wantsNotification(NotificationType::NewComment)) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'             => NotificationType::NewComment->value,
            'comment_id'       => $this->comment->id,
            'comment_excerpt'  => \Illuminate\Support\Str::limit($this->comment->content, 80),
            'commenter_id'     => $this->commenter->id,
            'commenter_name'   => $this->commenter->name,
            'commenter_username' => $this->commenter->username,
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id'   => $this->comment->commentable_id,
        ];
    }

    /**
     * Get the broadcast representation.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
