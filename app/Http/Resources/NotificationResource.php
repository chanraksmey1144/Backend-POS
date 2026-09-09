<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       $typeVal = $this->type instanceof \BackedEnum ? $this->type->value : $this->type;
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'is_global'   => is_null($this->user_id),
            'type'        => $typeVal,
            'type_label'  => match ($typeVal) {
                'low_stock'    => 'Sắp hết hàng',
                'out_of_stock' => 'Hết hàng',
                'purchase'     => 'Nhập hàng',
                'sales'        => 'Bán hàng',
                'return'       => 'Trả hàng',
                'payment'      => 'Thanh toán',
                'system'       => 'Hệ thống',
                default        => 'Thông báo',
            },
            'badge_color' => match ($typeVal) {
                'out_of_stock' => 'danger',
                'low_stock'    => 'warning',
                'purchase'     => 'info',
                'sales'        => 'success',
                'payment'      => 'primary',
                default        => 'secondary',
            },
            'title'       => $this->title,
            'message'     => $this->message,
            'is_read'     => (bool) $this->is_read,
            'link'        => $this->link,
            'created_at'  => $this->created_at?->toISOString(),
            'time_ago'    => $this->created_at?->diffForHumans(),
        ];
    }
}
