<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnResource extends JsonResource
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
            'sale_id'       => $this->sale_id,
            'sale'          => new SaleResource($this->whenLoaded('sale')),
            'branch_id'     => $this->branch_id,
            'branch'        => new BranchResource($this->whenLoaded('branch')),
            'cashier_id'    => $this->cashier_id,
            'cashier'       => new UserResource($this->whenLoaded('cashier')),
            'reason'        => $this->reason,
            'refund_amount' => (float) $this->refund_amount,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),

            'items' => ReturnItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
