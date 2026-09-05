<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of users with filters & relations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::with(['role', 'branch']);
        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->query('role_id'));
        }
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        // Search by name, email, or phone
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $users = $query->latest()->paginate($request->integer('per_page', 15));
        return UserResource::collection($users);
    }
    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        // Map 'password' request parameter to 'password_hash' column
        $validated['password_hash'] = $validated['password'];
        unset($validated['password']);
        $user = User::create($validated);
        $user->load(['role', 'branch']);
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
    /**
     * Display the specified user.
     */
    public function show(User $user): UserResource
    {
        $user->load(['role', 'branch']);
        return new UserResource($user);
    }
    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $validated = $request->validated();
        // Hash and update password only if provided
        if (!empty($validated['password'])) {
            $validated['password_hash'] = $validated['password'];
        }
        unset($validated['password']);
        $user->update($validated);
        $user->load(['role', 'branch']);
        return new UserResource($user);
    }
    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
