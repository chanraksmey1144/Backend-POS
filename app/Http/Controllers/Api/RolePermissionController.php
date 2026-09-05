<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Get all permissions assigned to the specified role.
     */
    public function index(Role $role): JsonResponse
    {
        $permissions = $role->permissions()
            ->pluck('permission')
            ->values();
        return response()->json([
            'role_id'     => $role->id,
            'role_key'    => $role->key,
            'grant_all'   => (bool) $role->grant_all,
            'permissions' => $permissions,
        ]);
    }
    /**
     * Sync / replace all permissions for the specified role.
     */
    public function sync(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string', 'max:100', 'distinct'],
        ]);
        DB::transaction(function () use ($role, $validated) {
            // Remove existing permissions
            $role->permissions()->delete();
            // Insert new permissions
            $records = array_map(fn($permission) => [
                'role_id'    => $role->id,
                'permission' => $permission,
                'created_at' => now(),
            ], $validated['permissions']);
            if (!empty($records)) {
                RolePermission::insert($records);
            }
        });
        $updatedPermissions = $role->permissions()
            ->pluck('permission')
            ->values();
        return response()->json([
            'message'     => 'Role permissions updated successfully.',
            'role_id'     => $role->id,
            'role_key'    => $role->key,
            'permissions' => $updatedPermissions,
        ], 200);
    }   
}
