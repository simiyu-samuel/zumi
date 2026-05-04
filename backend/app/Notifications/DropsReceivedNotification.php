<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class DropsReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $sender,
        protected int $amount,
        protected ?Model $source = null
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && 
            !$notifiable->wantsNotification(NotificationType::DropsReceived)) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'         => NotificationType::DropsReceived->value,
            'sender_id'    => $this->sender->id,
            'sender_name'  => $this->sender->name,
            'amount'       => $this->amount,
            'source_type'  => $this->source ? get_class($this->source) : null,
            'source_id'    => $this->source?->id,
            'message'      => "You received {$this->amount} Drops from {$this->sender->name}!",
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
