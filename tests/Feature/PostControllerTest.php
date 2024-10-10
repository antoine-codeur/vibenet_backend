<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test post creation.
     */
    public function test_create_post()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $blog = Blog::factory()->create(['owner_id' => $user->id]);

        $data = [
            'content' => 'This is a test post content.',
            'type' => 'text',
        ];

        $response = $this->postJson("/api/v1/blogs/{$blog->id}/posts", $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'content',
                         'type',
                         'blog_id',
                         'owner_id',
                     ],
                     'message',
                 ]);

        $this->assertDatabaseHas('posts', [
            'content' => 'This is a test post content.',
            'type' => 'text',
            'blog_id' => $blog->id,
            'owner_id' => $user->id,
        ]);
    }

    /**
     * Test post retrieval.
     */
    public function test_show_post()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['owner_id' => $user->id]);

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'content' => $post->content,
                ]);
    }

    /**
     * Test post update.
     */
    public function test_update_post()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['owner_id' => $user->id]);

        $data = [
            'content' => 'Updated post content',
            'type' => 'text',
        ];

        $response = $this->postJson("/api/v1/posts/{$post->id}/update", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'content' => 'Updated post content',
                 ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Updated post content',
        ]);
    }

    /**
     * Test post deletion.
     */
    public function test_delete_post()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['owner_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Post deleted successfully.',
                 ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * Test listing posts for a blog.
     */
    public function test_list_posts_for_blog()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Authentification de l'utilisateur

        $blog = Blog::factory()->create(['owner_id' => $user->id]);
        Post::factory(3)->create(['blog_id' => $blog->id]);

        $response = $this->getJson("/api/v1/blogs/{$blog->id}/posts");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'type',
                            'blog_id',
                            'owner_id',
                        ],
                    ],
                ]);
    }
}
