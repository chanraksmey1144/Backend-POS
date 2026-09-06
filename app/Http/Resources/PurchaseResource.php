<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
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
            'purchase_number' => $this->purchase_number,
            'order_date'      => $this->order_date?->toIso8601String(),
            'expected_date'   => $this->expected_date?->toIso8601String(),
            'received_at'     => $this->received_at?->toIso8601String(),
            'subtotal'        => (float) $this->subtotal,
            'discount'        => (float) $this->discount,
            'tax'             => (float) $this->tax,
            'total'           => (float) $this->total,
            'status'          => $this->status,
            'payment_status'  => $this->payment_status,
            'notes'           => $this->notes,
            'supplier_id'     => $this->supplier_id,
            'supplier'        => new SupplierResource($this->whenLoaded('supplier')),
            'branch_id'       => $this->branch_id,
            'branch'          => new BranchResource($this->whenLoaded('branch')),
            'warehouse_id'    => $this->warehouse_id,
            'warehouse'       => new WarehouseResource($this->whenLoaded('warehouse')),
            'created_by'      => $this->created_by,
            'creator'         => new UserResource($this->whenLoaded('creator')),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
