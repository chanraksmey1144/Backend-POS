<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'transfer_number'          => $this->transfer_number,
            'item_count'               => (int) $this->item_count,
            'status'                   => $this->status,
            'notes'                    => $this->notes,
            'source_warehouse_id'      => $this->source_warehouse_id,
            'source_warehouse'         => new WarehouseResource($this->whenLoaded('sourceWarehouse')),
            'destination_warehouse_id' => $this->destination_warehouse_id,
            'destination_warehouse'    => new WarehouseResource($this->whenLoaded('destinationWarehouse')),
            'created_by'               => $this->created_by,
            'creator'                  => new UserResource($this->whenLoaded('creator')),
            'created_at'               => $this->created_at?->toIso8601String(),
            'updated_at'               => $this->updated_at?->toIso8601String(),
            'items' => TransferItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
