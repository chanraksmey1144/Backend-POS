<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRegisterSessionResource extends JsonResource
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
            'register_id'   => $this->register_id,
            'register'      => new RegisterResource($this->whenLoaded('register')),
            'branch_id'     => $this->branch_id,
            'branch'        => new BranchResource($this->whenLoaded('branch')),
            'user_id'       => $this->user_id,
            'cashier'       => new UserResource($this->whenLoaded('user')),
            'opening_cash'  => (float) $this->opening_cash,
            'expected_cash' => (float) $this->expected_cash,
            'actual_cash'   => $this->actual_cash !== null ? (float) $this->actual_cash : null,
            'difference'    => $this->difference !== null ? (float) $this->difference : null,
            'status'        => $this->status,
            'opened_at'     => $this->opened_at?->toIso8601String(),
            'closed_at'     => $this->closed_at?->toIso8601String(),
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),

            'transactions' => CashTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
