<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'loyalty_points' => (int) $this->loyalty_points,
            'total_spent'    => (float) $this->total_spent,
            'outstanding'    => (float) $this->outstanding,
            'status'         => $this->status,
            'group_id'       => $this->group_id,
            'group'          => new CustomerGroupResource($this->whenLoaded('group')),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
