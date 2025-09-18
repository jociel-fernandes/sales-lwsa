<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF middleware for these feature tests to focus on auth logic
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
            if (!($response->isOk() || ($response->status() >= 200 && $response->status() < 300))) {
                $response->dump();
            }
            $this->assertTrue($response->isOk() || ($response->status() >= 200 && $response->status() < 300));
            $response->assertJson(['message' => 'Login successful']);
    $this->assertAuthenticated('web');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

            $response = $this->actingAs($user, 'web')
                ->postJson('/auth/logout');
    $this->assertGuest();
    $response->assertOk()->assertJson(['message' => 'Logout successful']);
    }
}
