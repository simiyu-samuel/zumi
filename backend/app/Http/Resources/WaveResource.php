<?php

namespace App\Http\Resources;

use App\Enums\WaveVisibility;
use App\Models\Wave;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => new UserResource($this->whenLoaded('user')),
            'circle_id'      => $this->circle_id,
            'circle'         => new CircleResource($this->whenLoaded('circle')),
            'title'          => $this->title,
            'description'    => $this->description,
            'stream_id'      => $this->stream_id,
            'thumbnail_url'  => $this->thumbnail_url,
            'visibility'     => $this->visibility,
            'gated_drops'    => $this->gated_drops,
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,
            'shares_count'   => $this->shares_count,
            'views_count'    => $this->views_count,
            'status'         => $this->status,
            'is_liked'       => $this->when(auth('sanctum')->check(), function () {
                return $this->likes()->where('user_id', auth('sanctum')->id())->exists();
            }),
            'mentions'       => $this->getMentions($this->title . ' ' . ($this->description ?? '')),
            'is_unlocked'    => $this->when(auth('sanctum')->check(), function () {
                if ($this->visibility !== WaveVisibility::Gated) {
                    return true;
                }
                if ($this->user_id === auth('sanctum')->id()) {
                    return true;
                }
                return $this->purchases()->where('user_id', auth('sanctum')->id())->exists();
            }, function () {
                return $this->visibility !== WaveVisibility::Gated;
            }),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }

    /**
     * Extract mentions from text.
     */
    protected function getMentions(?string $text): array
    {
        if (!$text) return [];

        preg_match_all('/(?<=^|\s)@([a-zA-Z0-9_]+)/', $text, $matches);
        $usernames = array_unique($matches[1]);

        if (empty($usernames)) return [];

        return \App\Models\User::whereIn('username', $usernames)
            ->get(['id', 'username', 'name'])
            ->toArray();
    }
}
