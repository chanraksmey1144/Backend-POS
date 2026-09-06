<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'contact_person'  => $this->contact_person,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'tax_number'      => $this->tax_number,
            'address'         => $this->address,
            'total_purchases' => (float) $this->total_purchases,
            'outstanding'     => (float) $this->outstanding,
            'status'          => $this->status,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
