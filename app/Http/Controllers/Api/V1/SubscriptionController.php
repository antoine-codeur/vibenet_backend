<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Models\User;
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
        $subscribedBlogs = $user->subscribedBlogs; // Assuming 'subscribedBlogs' relationship is defined in User model

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

        return $this->sendResponse([], 'Successfully unsubscribed from the blog.');
    }
}
