<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CircleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'type'          => $this->type,
            'status'        => $this->status,
            'avatar'        => $this->avatar,
            'cover_image'   => $this->cover_image,
            'members_count' => $this->members_count,
            'owner'         => new UserResource($this->whenLoaded('owner')),
            'is_member'     => $this->when(auth('sanctum')->check(), function () {
                return $this->members()->where('user_id', auth('sanctum')->id())->exists();
            }),
            'has_pending_request' => $this->when(auth('sanctum')->check(), function () {
                return $this->joinRequests()->where('user_id', auth('sanctum')->id())->where('status', 'pending')->exists();
            }),
            'is_owner'      => auth('sanctum')->id() === $this->owner_id,
            'created_at'    => $this->created_at,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'seo' => [
                    'title'       => $this->name . ' | Zumi Circle',
                    'description' => $this->description ?? 'Join this community on Zumi!',
                    'image'       => $this->avatar,
                    'type'        => 'profile',
                ]
            ]
        ];
    }
}
