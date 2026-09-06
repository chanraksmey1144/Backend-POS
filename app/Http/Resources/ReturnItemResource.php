<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'return_id'  => $this->return_id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'name'       => $this->name,
            'sku'        => $this->sku,
            'price'      => (float) $this->price,
            'cost'       => (float) $this->cost,
            'quantity'   => (float) $this->quantity,
            'discount'   => (float) $this->discount,
            'tax'        => (float) $this->tax,
            'line_total' => (float) $this->line_total,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
