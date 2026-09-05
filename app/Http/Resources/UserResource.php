<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'email'                => $this->email,
            'phone'                => $this->phone,
            'avatar'               => $this->avatar,
            'status'               => $this->status,
            'must_change_password' => (bool) $this->must_change_password,
            'last_login_at'        => $this->last_login_at?->toIso8601String(),
            'role_id'              => $this->role_id,
            'role'                 => new RoleResource($this->whenLoaded('role')),
            'branch_id'            => $this->branch_id,
            'branch'               => new BranchResource($this->whenLoaded('branch')),
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
