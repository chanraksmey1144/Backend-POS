<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'sale_date'      => $this->sale_date?->toIso8601String(),
            'subtotal'       => (float) $this->subtotal,
            'discount'       => (float) $this->discount,
            'tax'            => (float) $this->tax,
            'total'          => (float) $this->total,
            'paid'           => (float) $this->paid,
            'change'         => (float) $this->change,
            'payment_method' => $this->payment_method,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'notes'          => $this->notes,
            'customer_id'    => $this->customer_id,
            'customer'       => new CustomerResource($this->whenLoaded('customer')),
            'cashier_id'     => $this->cashier_id,
            'cashier'        => new UserResource($this->whenLoaded('cashier')),
            'branch_id'      => $this->branch_id,
            'branch'         => new BranchResource($this->whenLoaded('branch')),
            'register_id'    => $this->register_id,
            'register'       => new RegisterResource($this->whenLoaded('register')),
            'created_by'     => $this->created_by,
            'creator'        => new UserResource($this->whenLoaded('creator')),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),

            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
