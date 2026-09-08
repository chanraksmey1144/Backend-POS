<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
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
            'movement_date' => $this->movement_date?->toIso8601String(),
            'type'          => $this->type,
            'quantity'      => (float) $this->quantity,
            'before_stock'  => $this->before_stock !== null ? (float) $this->before_stock : null,
            'after_stock'   => $this->after_stock !== null ? (float) $this->after_stock : null,
            'reference'     => $this->reference,
            'note'          => $this->note,
            'product_id'    => $this->product_id,
            'product'       => new ProductResource($this->whenLoaded('product')),
            'variant_id'    => $this->variant_id,
            'variant'       => new ProductVariantResource($this->whenLoaded('variant')),
            'warehouse_id'  => $this->warehouse_id,
            'warehouse'     => new WarehouseResource($this->whenLoaded('warehouse')),
            'user_id'       => $this->user_id,
            'user'          => new UserResource($this->whenLoaded('user')),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
