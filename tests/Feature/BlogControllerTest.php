<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test blog creation.
     */
    public function test_create_blog()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Simule un utilisateur authentifié

        $data = [
            'name' => 'Mon premier blog',
            'description' => 'Ceci est une description',
        ];

        $response = $this->postJson('/api/v1/blogs', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'description',
                         'owner_id',
                     ],
                     'message',
                 ]);

        // Vérifie que le blog est bien dans la base de données
        $this->assertDatabaseHas('blogs', [
            'name' => 'Mon premier blog',
            'description' => 'Ceci est une description',
        ]);
    }

    /**
     * Test blog update.
     */
    public function test_update_blog()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $blog = Blog::factory()->create(['owner_id' => $user->id]);

        $data = [
            'name' => 'Nom mis à jour',
            'description' => 'Description mise à jour',
        ];

        $response = $this->postJson("/api/v1/blogs/{$blog->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Nom mis à jour',
                     'description' => 'Description mise à jour',
                 ]);

        $this->assertDatabaseHas('blogs', [
            'id' => $blog->id,
            'name' => 'Nom mis à jour',
            'description' => 'Description mise à jour',
        ]);
    }

    /**
     * Test blog deletion.
     */
    public function test_delete_blog()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $blog = Blog::factory()->create(['owner_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/blogs/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Blog deleted successfully.',
                 ]);

        $this->assertDatabaseMissing('blogs', [
            'id' => $blog->id,
        ]);
    }

    /**
     * Test blog listing.
     */
    public function test_list_blogs()
    {
        Blog::factory(5)->create();

        $response = $this->getJson('/api/v1/explore');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'description',
                         ],
                     ],
                 ]);
    }

    /**
     * Test subscribing to a blog.
     */
    public function test_subscribe_to_blog()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/subscriptions/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully subscribed to the blog.',
                 ]);

        // Vérifie que l'utilisateur est abonné au blog
        $this->assertTrue($user->subscribedBlogs()->where('blog_id', $blog->id)->exists());
    }

    /**
     * Test unsubscribing from a blog.
     */
    public function test_unsubscribe_from_blog()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();

        $user->subscribedBlogs()->attach($blog->id);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/subscriptions/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully unsubscribed from the blog and removed from folders.',
                 ]);

        // Vérifie que l'utilisateur n'est plus abonné
        $this->assertFalse($user->subscribedBlogs()->where('blog_id', $blog->id)->exists());
    }
}
