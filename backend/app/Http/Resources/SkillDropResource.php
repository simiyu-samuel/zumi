<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillDropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwner = $user && $user->id === $this->user_id;
        $isBuyer = $user && $this->buyers()->where('user_id', $user->id)->exists();

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price_drops' => $this->price_drops,
            'preview_url' => $this->preview_url,
            // Only expose content_url to owner or buyers
            'content_url' => ($isOwner || $isBuyer) ? $this->content_url : null,
            'sales_count' => $this->sales_count,
            'rating_avg'  => $this->rating_avg,
            'created_at'  => $this->created_at,
            'user'        => new UserResource($this->whenLoaded('user')),
            'is_owned'    => $isOwner,
            'is_purchased'=> $isBuyer,
        ];
    }
}
