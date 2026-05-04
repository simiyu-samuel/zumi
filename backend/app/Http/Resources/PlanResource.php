<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->resource['price_id'],
            'key'      => $this->resource['key'], // I'll add the key in index()
            'name'     => $this->resource['name'],
            'amount'   => $this->resource['amount'],
            'currency' => 'usd',
        ];
    }
}
