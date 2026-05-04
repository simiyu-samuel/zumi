<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DropsTransactionResource extends JsonResource
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
            'type'           => $this->type,
            'amount'         => $this->amount,
            'direction'      => $this->direction,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'status'         => $this->status,
            'metadata'       => $this->metadata,
            'created_at'     => $this->created_at,
        ];
    }
}
