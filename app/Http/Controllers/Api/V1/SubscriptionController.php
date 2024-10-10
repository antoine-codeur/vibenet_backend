<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/subscriptions",
     *     summary="Get subscribed blogs",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of subscribed blogs",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Blog"))
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        $subscribedBlogs = $user->subscribedBlogs;

        return $this->sendResponse($subscribedBlogs, 'Subscribed blogs retrieved successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/{blogId}",
     *     summary="Subscribe to a blog",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="blogId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully subscribed to the blog",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found",
     *     )
     * )
     */
    public function subscribe($blogId)
    {
        $user = Auth::user();
        $blog = Blog::find($blogId);

        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        // Subscribe the user to the blog
        $user->subscribedBlogs()->attach($blogId);

        return $this->sendResponse([], 'Successfully subscribed to the blog.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/subscriptions/{blogId}",
     *     summary="Unsubscribe from a blog",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="blogId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully unsubscribed from the blog",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found",
     *     )
     * )
     */
    public function unsubscribe($blogId)
    {
        $user = Auth::user();
        $blog = Blog::find($blogId);

        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        // Unsubscribe the user from the blog
        $user->subscribedBlogs()->detach($blogId);

        // Remove the blog from all folders the user has
        $user->folders()->whereHas('blogs', function ($query) use ($blogId) {
            $query->where('blog_id', $blogId);
        })->each(function ($folder) use ($blogId) {
            $folder->blogs()->detach($blogId);

            // If the folder is empty, delete it
            if ($folder->blogs()->count() === 0) {
                $folder->delete();
            }
        });

        return $this->sendResponse([], 'Successfully unsubscribed from the blog and removed from folders.');
    }
}
