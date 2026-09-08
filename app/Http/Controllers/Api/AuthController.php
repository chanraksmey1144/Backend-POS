<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Nette\Schema\ValidationException;

class AuthController extends Controller
{
   /**
     * Authenticate user & issue Sanctum Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::with(['role', 'branch'])->where('email', $request->email)->first();
        // Check password against password_hash column
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'errors'  => [
                    'email' => ['The provided credentials do not match our records.'],
                ],
            ], 401);
        }
        // Check if user account is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact administrator.',
            ], 403);
        }
        // Update last login timestamp
        $user->update(['last_login_at' => now()]);
        // Create Sanctum personal access token
        $token = $user->createToken('pos-api-token')->plainTextToken;
        return response()->json([
            'message'      => 'Login successful.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => new UserResource($user),
        ], 200);
    }
    /**
     * Get the authenticated user details.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'branch']);
        return response()->json([
            'user' => new UserResource($user),
        ], 200);
    }
    /**
     * Revoke the current access token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}