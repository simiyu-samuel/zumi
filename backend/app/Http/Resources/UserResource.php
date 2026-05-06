<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'username'             => $this->username,
            'email'                => $this->email,
            'bio'                  => $this->bio,
            'avatar_url'           => $this->getFirstMediaUrl(\App\Models\User::COLLECTION_AVATAR),
            'banner_url'           => $this->getFirstMediaUrl(\App\Models\User::COLLECTION_BANNER),
            'drops_balance'        => $this->drops_balance,
            'flow_score'           => $this->flow_score ?? 0,
            'is_following'         => $request->user() ? $this->followers()->where('follower_id', $request->user()->id)->exists() : false,
            'followers_count'      => $this->followers()->count(),
            'following_count'      => $this->following()->count(),
            'onboarding_completed' => $this->onboarding_completed,
            'verified_at'          => $this->verified_at,
            'role'                 => $this->role,
            'status'               => $this->status,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'seo' => [
                    'title'       => $this->name . ' (@' . $this->username . ') | Zumi',
                    'description' => $this->bio ?? 'Connect with me on Zumi!',
                    'image'       => $this->getFirstMediaUrl(\App\Models\User::COLLECTION_AVATAR),
                    'type'        => 'profile',
                ]
            ]
        ];
    }
}
