<?php

namespace App\Notifications;

use App\Models\CircleJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Queue\SerializesModels;

class CircleJoinRequestNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected CircleJoinRequest $request
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && 
            !$notifiable->wantsNotification(\App\Enums\NotificationType::CircleRequest)) {
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
            'type'          => \App\Enums\NotificationType::CircleRequest->value,
            'request_id'    => $this->request->id,
            'circle_id'     => $this->request->circle_id,
            'circle_name'   => $this->request->circle->name,
            'requester_id'  => $this->request->user_id,
            'requester_name'=> $this->request->user->name,
            'message'       => "{$this->request->user->name} requested to join {$this->request->circle->name}.",
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
