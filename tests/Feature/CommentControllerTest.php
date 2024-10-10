<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test comment creation.
     */
    public function test_create_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $data = [
            'content' => 'This is a test comment.',
        ];

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", $data);

        // Correction : on vérifie le fragment JSON dans 'data'
        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => 'This is a test comment.']);
    }

    /**
     * Test comment update.
     */
    public function test_update_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['owner_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        $data = [
            'content' => 'Updated comment content',
        ];

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/comments/{$comment->id}/update", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => 'Updated comment content']);
    }

    /**
     * Test comment deletion.
     */
    public function test_delete_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['owner_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Comment deleted successfully.']);
    }

    /**
     * Test toggle comment visibility.
     */
    public function test_toggle_comment_visibility()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['owner_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id, 'is_visible' => true]);

        $this->actingAs($user);
        $response = $this->putJson("/api/v1/comments/{$comment->id}/toggle");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Comment visibility toggled.']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'is_visible' => false,
        ]);
    }
}
