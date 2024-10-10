<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching subscribed blogs.
     */
    public function test_get_subscribed_blogs()
    {
        $user = User::factory()->create();
        $blogs = Blog::factory()->count(3)->create();

        // Simulate user subscribing to blogs
        $user->subscribedBlogs()->attach($blogs->pluck('id'));

        $this->actingAs($user);
        $response = $this->getJson('/api/v1/subscriptions');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonFragment(['id' => $blogs[0]->id])
                 ->assertJsonFragment(['id' => $blogs[1]->id])
                 ->assertJsonFragment(['id' => $blogs[2]->id]);
    }

    /**
     * Test subscribing to a blog.
     */
    public function test_subscribe_to_blog()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/subscriptions/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully subscribed to the blog.']);

        // Assert the user is subscribed to the blog
        $this->assertDatabaseHas('blog_user', [
            'user_id' => $user->id,
            'blog_id' => $blog->id,
        ]);
    }

    /**
     * Test trying to subscribe to a non-existing blog.
     */
    public function test_subscribe_to_non_existing_blog()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson('/api/v1/subscriptions/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Blog not found.']);
    }

    /**
     * Test unsubscribing from a blog.
     */
    public function test_unsubscribe_from_blog()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();

        // Simulate user subscribing to the blog
        $user->subscribedBlogs()->attach($blog->id);

        $this->actingAs($user);
        $response = $this->deleteJson("/api/v1/subscriptions/{$blog->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Successfully unsubscribed from the blog and removed from folders.',
                ]);

        // Assert the user is unsubscribed
        $this->assertDatabaseMissing('blog_user', [
            'user_id' => $user->id,
            'blog_id' => $blog->id,
        ]);
    }

    /**
     * Test unsubscribing from a blog that does not exist.
     */
    public function test_unsubscribe_from_non_existing_blog()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->deleteJson('/api/v1/subscriptions/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Blog not found.']);
    }

    /**
     * Test unsubscribing from a blog removes it from folders.
     */
    public function test_unsubscribe_removes_blog_from_folders()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        // Simulate user subscribing to the blog and adding it to a folder
        $user->subscribedBlogs()->attach($blog->id);
        $folder->blogs()->attach($blog->id);

        $this->actingAs($user);
        $response = $this->deleteJson("/api/v1/subscriptions/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully unsubscribed from the blog and removed from folders.']);

        // Assert the blog is removed from the folder
        $this->assertDatabaseMissing('folder_blog', [
            'folder_id' => $folder->id,
            'blog_id' => $blog->id,
        ]);

        // Assert the folder is deleted because it's empty
        $this->assertDatabaseMissing('folders', [
            'id' => $folder->id,
        ]);
    }
}
