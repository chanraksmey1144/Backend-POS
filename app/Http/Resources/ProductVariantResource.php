<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product'    => new ProductResource($this->whenLoaded('product')),
            'name'       => $this->name,
            'sku'        => $this->sku,
            'barcode'    => $this->barcode,
            'cost'       => (float) $this->cost,
            'price'      => (float) $this->price,
            'stock'      => (float) $this->stock,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
