<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     */
    public function test_register_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Send a POST request to register endpoint
        $response = $this->postJson('/api/v1/register', $data);

        // Assert that the response is successful and contains a token
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'token',
                         'name',
                     ],
                     'message',
                 ]);

        // Assert that the user is created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test user login with valid credentials.
     */
    public function test_login_user()
    {
        // Create a user to test login
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login with the created user
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        // Assert successful login and presence of token
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'token',
                         'name',
                     ],
                     'message',
                 ]);

        // Check if the response contains the correct user name
        $response->assertJsonFragment(['name' => $user->name]);
    }

    /**
     * Test access to protected route without authentication.
     */
    public function test_access_protected_route_without_authentication()
    {
        // Attempt to access a protected route without being authenticated
        $response = $this->getJson('/api/v1/profile');

        // Assert that the response returns an unauthenticated error
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }
}
