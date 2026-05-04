<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'reporter_id'   => $this->reporter_id,
            'reported_type' => $this->reported_type,
            'reported_id'   => $this->reported_id,
            'reason'        => $this->reason,
            'description'   => $this->description,
            'status'        => $this->status,
            'moderator_notes' => $this->moderator_notes,
            'resolved_at'   => $this->resolved_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'reporter'      => new UserResource($this->whenLoaded('reporter')),
            'reported'      => $this->whenLoaded('reported'),
        ];
    }
}
