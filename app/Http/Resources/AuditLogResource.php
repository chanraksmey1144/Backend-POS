<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actionVal = $this->action instanceof \BackedEnum ? $this->action->value : $this->action;
        
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'user_name'   => $this->user?->name ?? 'System',
            'action'      => $actionVal,
            'action_label'=> ucfirst($actionVal),
            'badge_color' => match ($actionVal) {
                'create', 'receive' => 'success',
                'update', 'adjust'  => 'info',
                'delete', 'cancel'  => 'danger',
                'archive'           => 'secondary',
                'login', 'logout'   => 'primary',
                'transfer'          => 'warning',
                'payment'           => 'success',
                'export'            => 'dark',
                default             => 'light',
            },
            'module'      => $this->module,
            'record'      => $this->record,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toISOString(),
            'time_ago'    => $this->created_at?->diffForHumans(),
        ];
    }
}
