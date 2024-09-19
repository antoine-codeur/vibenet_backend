<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{postId}/comments",
     *     summary="Create a new comment",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Comment")
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
    public function store(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $post = Post::find($postId);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_visible' => true, // Default to visible when created
        ]);

        return $this->sendResponse($comment, 'Comment created successfully.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{postId}/comments",
     *     summary="Get comments for a post",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Comment"))
     *     )
     * )
     */
    public function index($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return $this->sendError('Post not found.');
        }

        $userId = auth()->id();
        
        // Retrieve comments based on visibility rules
        $comments = Comment::where('post_id', $postId)
            ->where(function ($query) use ($userId) {
                $query->where('is_visible', true)
                    ->orWhere('user_id', $userId) // User can see their own comments
                    ->orWhere('post_id', function($subQuery) use ($userId) {
                        $subQuery->select('id')
                                    ->from('posts')
                                    ->where('owner_id', $userId); // Owner can see all comments on their posts
                    });
            })
            ->get();

        return $this->sendResponse($comments, 'Comments retrieved successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Comments"},
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
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        // Optionally check if the user is the owner of the comment or post before deleting
        $comment->delete();
        return $this->sendResponse([], 'Comment deleted successfully.');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comments/{id}/toggle",
     *     summary="Toggle comment visibility",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment visibility toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment visibility toggled.")
     *         )
     *     )
     * )
     */
    public function toggleVisibility($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->sendError('Comment not found.');
        }

        // Check if the user is the owner of the comment or the post
        if (auth()->id() !== $comment->user_id && auth()->id() !== $comment->post->owner_id) {
            return $this->sendError('Unauthorized to toggle comment visibility.');
        }

        // Toggle the visibility
        $comment->is_visible = !$comment->is_visible;
        $comment->save();

        return $this->sendResponse([], 'Comment visibility toggled.');
    }
}
