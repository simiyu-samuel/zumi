<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use App\Models\Wave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Queue\SerializesModels;

class WaveLikedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Wave $wave,
        public readonly User $liker,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') &&
            !$notifiable->wantsNotification(NotificationType::WaveLiked)) {
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
            'type'      => NotificationType::WaveLiked->value,
            'wave_id'   => $this->wave->id,
            'wave_title' => $this->wave->title,
            'liker_id'  => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_username' => $this->liker->username,
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
