<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing all blogs.
     */
    public function test_list_blogs()
    {
        $blogs = Blog::factory()->count(3)->create();

        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/admin/blogs');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test deleting a blog.
     */
    public function test_delete_blog()
    {
        $blog = Blog::factory()->create();

        $this->actingAsAdmin();
        $response = $this->deleteJson("/api/v1/admin/blogs/{$blog->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Blog deleted successfully.']);

        // Assert the blog is deleted
        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }

    /**
     * Test listing all posts.
     */
    public function test_list_posts()
    {
        $posts = Post::factory()->count(3)->create();

        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/admin/posts');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test deleting a post.
     */
    public function test_delete_post()
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin();
        $response = $this->deleteJson("/api/v1/admin/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Post deleted successfully.']);

        // Assert the post is deleted
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /**
     * Test listing all comments.
     */
    public function test_list_comments()
    {
        $comments = Comment::factory()->count(3)->create();

        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/admin/comments');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /**
     * Test deleting a comment.
     */
    public function test_delete_comment()
    {
        $comment = Comment::factory()->create();

        $this->actingAsAdmin();
        $response = $this->deleteJson("/api/v1/admin/comments/{$comment->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Comment deleted successfully.']);

        // Assert the comment is deleted
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /**
     * Test listing all users.
     */
    public function test_list_users()
    {
        $users = User::factory()->count(3)->create();

        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
                ->assertJsonCount(4, 'data');
    }

    /**
     * Test deleting a user.
     */
    public function test_delete_user()
    {
        $user = User::factory()->create();

        $this->actingAsAdmin();
        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully.']);

        // Assert the user is deleted
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test listing all uploaded files.
     */
    public function test_list_uploads()
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/testfile.jpg', 'Test Content');

        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/admin/uploads');

        $response->assertStatus(200)
                ->assertJsonFragment(['uploads/testfile.jpg']);
    }


    /**
     * Test deleting an uploaded file.
     */
    public function test_delete_file_in_subfolder()
    {
        Storage::fake('public');

        Storage::disk('public')->put('uploads/profile_pictures/testfile.jpg', 'Test Content');

        $this->actingAsAdmin();

        $response = $this->deleteJson('/api/v1/admin/uploads/profile_pictures/testfile.jpg');

        $response->assertStatus(200)
                ->assertJson(['message' => 'File deleted successfully.']);

        Storage::disk('public')->assertMissing('uploads/profile_pictures/testfile.jpg');
    }

    /**
     * Helper to act as admin.
     */
    protected function actingAsAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
    }
}
