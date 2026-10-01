<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_response_does_not_disclose_unknown_emails(): void
    {
        Notification::fake();
        $user = $this->createUser();

        $known = $this->postJson('/api/auth/forgot-password', ['email' => $user->email]);
        $unknown = $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.com']);

        $known->assertOk()->assertJson(['success' => true]);
        $unknown->assertOk()->assertExactJson($known->json());
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_changes_password_and_revokes_existing_tokens(): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);
        $accessToken = $user->createToken('before-reset')->accessToken;

        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'UpdatedPassword123!',
            'password_confirmation' => 'UpdatedPassword123!',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('UpdatedPassword123!', $user->fresh()->password_hash));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $accessToken->id]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Reset Test User',
            'email' => 'reset@example.com',
            'password_hash' => 'CurrentPassword123!',
            'status' => 'active',
        ]);
    }
}