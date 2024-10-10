<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test folder creation with a blog.
     */
    public function test_create_folder_with_blog()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();

        $data = [
            'name' => 'My New Folder',
            'blog_id' => $blog->id,
        ];

        $this->actingAs($user);
        $response = $this->postJson('/api/v1/folders', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'My New Folder']);

        // Assert the folder is created in the database
        $this->assertDatabaseHas('folders', [
            'name' => 'My New Folder',
            'user_id' => $user->id,
        ]);

        // Assert the blog is attached to the folder
        $this->assertDatabaseHas('folder_blog', [
            'blog_id' => $blog->id,
        ]);
    }

    /**
     * Test that a blog cannot be added to multiple folders.
     */
    public function test_cannot_add_blog_to_multiple_folders()
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create();
        $folder1 = Folder::factory()->create(['user_id' => $user->id]);
        $folder1->blogs()->attach($blog->id);

        $folder2 = Folder::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/folders/{$folder2->id}/add-blog", ['blog_id' => $blog->id]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['blog_id' => 'This blog is already in another folder.']);
    }

    /**
     * Test adding a blog to a folder.
     */
    public function test_add_blog_to_folder()
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);
        $blog = Blog::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/folders/{$folder->id}/add-blog", ['blog_id' => $blog->id]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Blog added to folder successfully.']);

        // Assert the blog is attached to the folder
        $this->assertDatabaseHas('folder_blog', [
            'folder_id' => $folder->id,
            'blog_id' => $blog->id,
        ]);
    }

    /**
     * Test removing a blog from a folder.
     */
    public function test_remove_blog_from_folder()
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);
        $blog = Blog::factory()->create();

        // Attach the blog to the folder
        $folder->blogs()->attach($blog->id);

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/folders/{$folder->id}/remove-blog", ['blog_id' => $blog->id]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Blog removed from folder successfully.']);

        // Assert the blog is detached from the folder
        $this->assertDatabaseMissing('folder_blog', [
            'folder_id' => $folder->id,
            'blog_id' => $blog->id,
        ]);
    }

    /**
     * Test removing a blog from a folder and deleting the folder if empty.
     */
    public function test_remove_blog_and_delete_empty_folder()
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);
        $blog = Blog::factory()->create();

        // Attach the blog to the folder
        $folder->blogs()->attach($blog->id);

        $this->actingAs($user);
        $response = $this->postJson("/api/v1/folders/{$folder->id}/remove-blog", ['blog_id' => $blog->id]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Blog removed from folder successfully.']);

        // Assert the blog is detached from the folder
        $this->assertDatabaseMissing('folder_blog', [
            'folder_id' => $folder->id,
            'blog_id' => $blog->id,
        ]);

        // Assert the folder is deleted since it's empty
        $this->assertDatabaseMissing('folders', [
            'id' => $folder->id,
        ]);
    }

    /**
     * Test listing folders with blogs.
     */
    public function test_list_folders_with_blogs()
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);
        $blog = Blog::factory()->create();

        // Attach the blog to the folder
        $folder->blogs()->attach($blog->id);

        $this->actingAs($user);
        $response = $this->getJson('/api/v1/folders');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $folder->name])
                 ->assertJsonFragment(['id' => $blog->id]);
    }
}
