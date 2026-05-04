<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [
                'active' => false,
                'tier'   => 'free',
            ];
        }

        return [
            'active'          => $this->active(),
            'tier'            => $this->type, // 'default' or custom
            'stripe_id'       => $this->stripe_id,
            'stripe_status'   => $this->stripe_status,
            'stripe_price'    => $this->stripe_price,
            'on_grace_period' => $this->onGracePeriod(),
            'ends_at'         => $this->ends_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
