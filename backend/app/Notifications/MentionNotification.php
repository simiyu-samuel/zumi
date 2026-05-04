<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Queue\SerializesModels;

class MentionNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Model $sourceModel,
        protected string $messageSnippet
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && 
            !$notifiable->wantsNotification(\App\Enums\NotificationType::Mention)) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => \App\Enums\NotificationType::Mention->value,
            'source_type'    => get_class($this->sourceModel),
            'source_id'      => $this->sourceModel->id,
            'message'        => $this->messageSnippet,
            'creator_id'     => $this->sourceModel->user_id,
            'creator_name'   => $this->sourceModel->user->name ?? 'Someone',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
