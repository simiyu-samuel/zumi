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
            'onboarding_completed' => $this->onboarding_completed,
            'verified_at'          => $this->verified_at,
            'role'                 => $this->role,
            'status'               => $this->status,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
