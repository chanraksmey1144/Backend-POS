<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate the user and return a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => 'Your account is inactive. Please contact an administrator.',
            ], 403);
        }

        $user->load(['role', 'branch']);
        $user->update(['last_login_at' => now()]);

        $role = $user->role;
        $permissions = $role?->grant_all
            ? ['*']
            : ($role?->permissions()->pluck('permission')->values()->all() ?? []);

        return response()->json([
            'message'     => 'Login successful.',
            'user'        => new UserResource($user),
            'token'       => $user->createToken('pos-token')->plainTextToken,
            'role'        => $role?->key,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['role', 'branch']);

        $role = $user->role;
        $permissions = $role?->grant_all
            ? ['*']
            : ($role?->permissions()->pluck('permission')->values()->all() ?? []);

        return response()->json([
            'user'        => new UserResource($user),
            'role'        => $role?->key,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}