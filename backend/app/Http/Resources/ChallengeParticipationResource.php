<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeParticipationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => new UserResource($this->whenLoaded('user')),
            'wave'        => new WaveResource($this->whenLoaded('wave')),
            'votes_count' => $this->votes_count,
            'created_at'  => $this->created_at,
        ];
    }
}
