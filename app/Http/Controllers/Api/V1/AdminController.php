<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Blog;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/blogs",
     *     summary="List all blogs",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Blogs retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Blog"))
     *     )
     * )
     */
    public function listBlogs()
    {
        $blogs = Blog::all();
        return $this->sendResponse($blogs, 'Blogs retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/blogs/{id}",
     *     summary="Delete a blog",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Blog deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Blog not found."),
     *         )
     *     )
     * )
     */
    public function destroyBlog($id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        $blog->delete();
        return $this->sendResponse([], 'Blog deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/posts",
     *     summary="List all posts",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Post"))
     *     )
     * )
     */
    public function listPosts()
    {
        $posts = Post::all();
        return $this->sendResponse($posts, 'Posts retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/posts/{id}",
     *     summary="Delete a post",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post not found."),
     *         )
     *     )
     * )
     */
    public function destroyPost($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        // Set image URL to empty and append a note before deletion
        $post->update(['image_url' => '', 'content' => $post->content . ' [This image has been removed.]']);
        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/comments",
     *     summary="List all comments",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Comments retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Comment"))
     *     )
     * )
     */
    public function listComments()
    {
        $comments = Comment::with(['post', 'user'])->get();
        return $this->sendResponse($comments, 'Comments retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Comment not found."),
     *         )
     *     )
     * )
     */
    public function destroyComment($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        $comment->delete();
        return $this->sendResponse([], 'Comment deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     summary="List all users",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function listUsers()
    {
        $users = User::all();
        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{id}",
     *     summary="Delete a user",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *         )
     *     )
     * )
     */
    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('User not found.');
        }

        $user->delete();
        return $this->sendResponse([], 'User deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/uploads",
     *     summary="List all uploaded files",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Uploaded files retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(type="string"))
     *     )
     * )
     */
    public function listUploads()
    {
        // Retrieve all files
        $files = Storage::disk('public')->allFiles('uploads/posts');

        // Filter out unwanted files like .DS_Store
        $filteredFiles = array_filter($files, function ($file) {
            return !preg_match('/\.(DS_Store|tmp|log)$/', basename($file)); // Exclude unwanted files
        });

        // Create an array with file paths and their modification times
        $fileDetails = [];
        foreach ($filteredFiles as $file) {
            $fileDetails[] = [
                'path' => $file,
                'last_modified' => Storage::disk('public')->lastModified($file),
            ];
        }

        // Sort the files by last modified date
        usort($fileDetails, function ($a, $b) {
            return $b['last_modified'] <=> $a['last_modified']; // Descending order
        });

        // Extract the sorted file paths
        $sortedFiles = array_column($fileDetails, 'path');

        \Log::info('Uploaded files:', $sortedFiles); // Log the retrieved files

        if (empty($sortedFiles)) {
            return $this->sendError('No uploaded files found.');
        }

        return $this->sendResponse($sortedFiles, 'Uploaded files retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/uploads/{filename}",
     *     summary="Delete an uploaded file",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found."),
     *         )
     *     )
     * )
     */
    public function deleteUpload($filename)
    {
        $filePath = 'public/uploads/' . $filename;
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return $this->sendResponse([], 'File deleted successfully.');
        }

        return $this->sendError('File not found.');
    }
}
