<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GatedRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isHost = $user && $user->id === $this->user_id;
        $isParticipant = $user && $this->participants()->where('user_id', $user->id)->exists();

        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'entry_fee_drops' => $this->entry_fee_drops,
            'status'          => $this->status,
            'scheduled_at'    => $this->scheduled_at,
            'started_at'      => $this->started_at,
            'ended_at'        => $this->ended_at,
            'host'            => new UserResource($this->whenLoaded('host')),
            'is_host'         => $isHost,
            'is_participant'  => $isParticipant,
            'participants_count' => $this->participants()->count(),
        ];
    }
}
