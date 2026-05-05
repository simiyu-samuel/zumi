<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentLiked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Comment $comment
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('waves.' . $this->comment->commentable_id . '.comments'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.liked';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'likes_count' => $this->comment->likes_count,
        ];
    }
}
