<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeldSaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'hold_number' => $this->hold_number,
            'customer_id' => $this->customer_id,
            'customer'    => new CustomerResource($this->whenLoaded('customer')),
            'cashier_id'  => $this->cashier_id,
            'cashier'     => new UserResource($this->whenLoaded('cashier')),
            'discount'    => (float) $this->discount,
            'tax'         => (float) $this->tax,
            'total'       => (float) $this->total,
            'items'       => $this->items_json, // JSON array decoded automatically
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
        
    }
}
