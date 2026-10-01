<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If an account exists for this email, a reset link has been sent.',
            'success' => true,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password_hash' => $password,
                ])->save();
                $user->tokens()->delete();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully.',
            'success' => true,
        ]);
    }

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
    /**
     * Update the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors'  => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        $user->update(['password_hash' => Hash::make($request->new_password)]);

        return response()->json([
            'message' => 'Password updated successfully.',
            'success' => true,
        ], 200);
    }
}