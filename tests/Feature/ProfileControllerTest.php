<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving the authenticated user's profile.
     */
    public function test_show_profile()
    {
        // Create a test user
        $user = User::factory()->create();

        // Simulate login
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/profile');

        // Assert that the response contains the user's details
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }

    /**
     * Test updating the user's profile.
     */
    public function test_update_profile()
    {
        // Create a test user
        $user = User::factory()->create();

        // Simulate login
        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'bio' => 'Updated bio information',
        ];

        // Send request to update profile
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile', $newData);

        // Assert successful response and that data is updated
        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'Updated Name',
                     'email' => 'updated@example.com',
                     'bio' => 'Updated bio information',
                 ]);

        // Verify the database has been updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test updating the user's profile picture.
     */
    public function test_update_profile_picture()
    {
        // Fake storage to test file uploads
        Storage::fake('public');

        // Create a test user
        $user = User::factory()->create();

        // Simulate login and send a profile picture upload
        $file = UploadedFile::fake()->image('profile.jpg');
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile', [
            'profile_picture' => $file,
        ]);

        // Assert the response is successful
        $response->assertStatus(200);

        // Check if the file was stored
        Storage::disk('public')->assertExists('uploads/profile_pictures/' . $file->hashName());

        // Verify the user's profile_picture field was updated in the database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'profile_picture' => 'uploads/profile_pictures/' . $file->hashName(),
        ]);
    }

    /**
     * Test deleting the user's profile.
     */
    public function test_delete_profile()
    {
        // Create a test user
        $user = User::factory()->create();

        // Simulate login
        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/profile');

        // Assert the profile was deleted successfully
        $response->assertStatus(200)
                 ->assertJson(['message' => 'User profile deleted successfully.']);

        // Verify the user no longer exists in the database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
