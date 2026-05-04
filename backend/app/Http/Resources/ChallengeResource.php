<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => new UserResource($this->whenLoaded('user')),
            'title'          => $this->title,
            'description'    => $this->description,
            'type'           => $this->type,
            'status'         => $this->status,
            'prize_pool'     => $this->prize_pool,
            'ends_at'        => $this->ends_at,
            'winner'         => new UserResource($this->whenLoaded('winner')),
            'participations' => ChallengeParticipationResource::collection($this->whenLoaded('participations')),
            'created_at'     => $this->created_at,
        ];
    }
}
