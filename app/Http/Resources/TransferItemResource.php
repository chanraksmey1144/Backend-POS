<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferItemResource extends JsonResource
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
            'transfer_id' => $this->transfer_id,
            'product_id'  => $this->product_id,
            'product'     => new ProductResource($this->whenLoaded('product')),
            'variant_id'  => $this->variant_id,
            'variant'     => new ProductVariantResource($this->whenLoaded('variant')),
            'quantity'    => (float) $this->quantity,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
