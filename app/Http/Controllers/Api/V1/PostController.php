<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/blogs/{blogId}/posts",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="blogId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"content"},
     *                 @OA\Property(property="content", type="string", example="This is a post content."),
     *                 @OA\Property(property="image", type="file", description="File to upload"),
     *                 @OA\Property(property="type", type="string", example="image"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $blogId)
    {
        // Define allowed file types
        $allowedFileTypes = $this->getAllowedFileTypes();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'content' => 'required',
            'image' => 'nullable|file|max:2048|mimetypes:' . implode(',', $allowedFileTypes), // Check mime types
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Handle file upload
        $fileUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (in_array($file->getMimeType(), $allowedFileTypes)) {
                $filePath = $file->store('uploads/posts', 'public');
                $fileUrl = '/storage/' . $filePath;
            } else {
                return $this->sendError('Invalid file type.');
            }
        }

        // Création du post
        $post = Post::create([
            'blog_id' => $blogId,
            'owner_id' => auth()->id(),
            'content' => $request->content,
            'image_url' => $fileUrl,
            'type' => $request->type,
        ]);

        return $this->sendResponse($post, 'Post created successfully.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/blogs/{blogId}/posts",
     *     summary="Get all posts for a blog",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="blogId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of posts",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Post"))
     *     )
     * )
     */
    public function index($blogId)
    {
        $posts = Post::where('blog_id', $blogId)->get();
        return $this->sendResponse($posts, 'Posts retrieved successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     summary="Get a post by ID",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
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
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        return $this->sendResponse($post, 'Post retrieved successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posts/{id}/update",
     *     summary="Update a post",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"content"},
     *                 @OA\Property(property="content", type="string", example="Updated post content."),
     *                 @OA\Property(property="image", type="file", description="File to upload"),
     *                 @OA\Property(property="type", type="string", example="image"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
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
    public function update(Request $request, $id)
    {
        \Log::info('Update request data: ', $request->all());

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'image' => 'nullable|file|max:2048|mimetypes:' . implode(',', $this->getAllowedFileTypes()),
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $post = Post::find($id);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        // Handle file upload and deletion of the old image
        $fileUrl = $post->image_url; // Keep existing URL if no new file is uploaded
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $this->deletePostImage($post->image_url);

            $file = $request->file('image');
            if (in_array($file->getMimeType(), $this->getAllowedFileTypes())) {
                $filePath = $file->store('uploads/posts', 'public');
                $fileUrl = '/storage/' . $filePath; // Update to new URL
            } else {
                return $this->sendError('Invalid file type.');
            }
        }

        // Update post with new values
        $post->update([
            'content' => $request->content,
            'image_url' => $fileUrl,
            'type' => $request->type,
        ]);

        return $this->sendResponse($post, 'Post updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     summary="Delete a post",
     *     tags={"Posts"},
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
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        // Delete image if it exists
        if ($post->image_url) {
            $this->deletePostImage($post->image_url);
        }

        $post->delete();
        return $this->sendResponse([], 'Post deleted successfully.');
    }

    public function deletePostImage($oldImageUrl)
    {
        if ($oldImageUrl) {
            Log::info("Checking existence of post image: $oldImageUrl");

            if (Storage::disk('public')->exists(str_replace('/storage/', '', $oldImageUrl))) {
                if (Storage::disk('public')->delete(str_replace('/storage/', '', $oldImageUrl))) {
                    Log::info("Post image deleted: $oldImageUrl");
                    return true;
                } else {
                    Log::error("Failed to delete post image: $oldImageUrl");
                    return false;
                }
            } else {
                Log::warning("Post image not found: $oldImageUrl");
                return false;
            }
        }

        Log::warning("No post image to delete.");
        return false;
    }

    protected function getAllowedFileTypes()
    {
        return [
            'image/webp',
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/svg+xml',
            'image/gif',
            'application/pdf',
            'text/plain',
            'text/markdown',
            'application/vnd.ms-excel', // for .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
            'application/octet-stream' // fallback for .vroid or other files
        ];
    }
}
