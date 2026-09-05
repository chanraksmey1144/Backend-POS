<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'sku'             => $this->sku,
            'barcode'         => $this->barcode,
            'description'     => $this->description,
            'image'           => [
                'label' => $this->image_label,
                'color' => $this->image_color,
            ],
                        'cost'            => (float) $this->cost,
            'price'           => (float) $this->price,
            'wholesale_price' => (float) $this->wholesale_price,
            'tax_percent'     => (float) $this->tax_percent,
            'track_inventory' => (bool) $this->track_inventory,
            'min_stock'       => (float) $this->min_stock,
            'max_stock'       => $this->max_stock !== null ? (float) $this->max_stock : null,
            'stock'           => (float) $this->stock,
            'is_low_stock'    => $this->track_inventory && ($this->stock <= $this->min_stock),
            'status'          => $this->status,
            'category_id'     => $this->category_id,
            'category'        => new CategoryResource($this->whenLoaded('category')),
            'brand_id'        => $this->brand_id,
            'brand'           => new BrandResource($this->whenLoaded('brand')),
            'unit_id'         => $this->unit_id,
            'unit'            => new UnitResource($this->whenLoaded('unit')),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
