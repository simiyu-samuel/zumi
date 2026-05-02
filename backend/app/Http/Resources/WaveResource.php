<?php

namespace App\Http\Resources;

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
            'is_unlocked'    => $this->when(auth('sanctum')->check(), function () {
                if ($this->visibility !== Wave::VISIBILITY_GATED) {
                    return true;
                }
                if ($this->user_id === auth('sanctum')->id()) {
                    return true;
                }
                return $this->purchases()->where('user_id', auth('sanctum')->id())->exists();
            }, function () {
                return $this->visibility !== Wave::VISIBILITY_GATED;
            }),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
