<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'session_id'       => $this->session_id,
            'transaction_type' => $this->transaction_type, // 'cash_in' | 'cash_out'
            'amount'           => (float) $this->amount,
            'description'      => $this->description,
            'user_id'          => $this->user_id,
            'cashier'          => new UserResource($this->whenLoaded('user')),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
