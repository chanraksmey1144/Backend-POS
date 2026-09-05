<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with optional search & pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Role::query();
        // Search by name, key, or description
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        // Filter by grant_all
        if ($request->has('grant_all')) {
            $query->where('grant_all', $request->boolean('grant_all'));
        }
        $roles = $query->latest()->paginate($request->integer('per_page', 15));
        return RoleResource::collection($roles);
    }
    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());
        return (new RoleResource($role))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified role.
     */
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }
    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $role->update($request->validated());
        return new RoleResource($role);
    }
    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deleting the primary superadmin role
        if ($role->grant_all) {
            return response()->json([
                'message' => 'Superadmin roles with grant_all permission cannot be deleted.',
            ], 403);
        }
        $role->delete();
        return response()->json([
            'message' => 'Role deleted successfully.',
        ], 200);
    }
}
