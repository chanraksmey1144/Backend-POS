<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
            'category'       => $this->category,
            'amount'         => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'expense_date'   => $this->expense_date?->toIso8601String(),
            'description'    => $this->description,
            'receipt'        => $this->receipt,
            'branch_id'      => $this->branch_id,
            'branch'         => new BranchResource($this->whenLoaded('branch')),
            'created_by'     => $this->created_by,
            'creator'        => new UserResource($this->whenLoaded('creator')),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
