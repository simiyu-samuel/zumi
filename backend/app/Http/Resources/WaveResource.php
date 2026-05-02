<?php

namespace App\Http\Resources;

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
            'user'           => new UserResource($this->whenLoaded('user')),
            'is_liked'       => $this->when($request->user(), function () use ($request) {
                return $this->likes()->where('user_id', $request->user()->id)->exists();
            }),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
