<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'content'    => $this->content,
            'user'       => new UserResource($this->whenLoaded('user')),
            'parent_id'  => $this->parent_id,
            'likes_count' => $this->likes_count ?? 0,
            'is_liked'   => $request->user() ? $this->isLikedBy($request->user()->id) : false,
            'replies'    => CommentResource::collection($this->whenLoaded('replies')),
            'mentions'   => $this->getMentions($this->content),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
