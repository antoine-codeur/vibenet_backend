<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Folder;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Folders",
 *     description="Operations related to folders"
 * )
 */
class FolderController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/folders",
     *     summary="Get all folders for the authenticated user, including blogs in each folder",
     *     tags={"Folders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of folders retrieved successfully, including blogs",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Folder"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();

        // Get the folders with their related blogs
        $folders = $user->folders()->with('blogs')->get();

        return $this->sendResponse($folders, 'Folders retrieved successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/folders",
     *     summary="Create a new folder",
     *     tags={"Folders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "blog_id"},
     *             @OA\Property(property="name", type="string", example="My Folder"),
     *             @OA\Property(property="blog_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Folder created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Folder")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'blog_id' => 'required|integer|exists:blogs,id',
        ]);

        $user = Auth::user();

        // Check if the blog is already in another folder of the user
        if ($user->folders()->whereHas('blogs', function ($query) use ($request) {
            $query->where('blog_id', $request->blog_id);
        })->exists()) {
            throw ValidationException::withMessages([
                'blog_id' => 'This blog is already in another folder.',
            ]);
        }

        // Create the folder and attach the blog to it
        $folder = Folder::create([
            'name' => $request->name,
            'user_id' => $user->id,
        ]);

        $folder->blogs()->attach($request->blog_id);

        return $this->sendResponse($folder->load('blogs'), 'Folder created successfully.', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/folders/{folderId}/add-blog",
     *     summary="Add a blog to a folder",
     *     tags={"Folders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blog_id"},
     *             @OA\Property(property="blog_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog added to folder successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function addBlogToFolder(Request $request, $folderId)
    {
        $request->validate([
            'blog_id' => 'required|integer|exists:blogs,id',
        ]);

        $user = Auth::user();
        $folder = Folder::findOrFail($folderId);

        // Check if the user owns the folder
        if ($folder->user_id !== $user->id) {
            return $this->sendError('Unauthorized.', 401);
        }

        // Check if the blog is already in any of the user's folders
        if ($user->folders()->whereHas('blogs', function ($query) use ($request) {
            $query->where('blog_id', $request->blog_id);
        })->exists()) {
            throw ValidationException::withMessages([
                'blog_id' => 'This blog is already in another folder.',
            ]);
        }

        // Add the blog to the folder
        $folder->blogs()->attach($request->blog_id);

        return $this->sendResponse($folder->load('blogs'), 'Blog added to folder successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/folders/{folderId}/remove-blog",
     *     summary="Remove a blog from a folder",
     *     tags={"Folders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blog_id"},
     *             @OA\Property(property="blog_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog removed from folder successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function removeBlogFromFolder(Request $request, $folderId)
    {
        $request->validate([
            'blog_id' => 'required|integer|exists:blogs,id',
        ]);

        $user = Auth::user();
        $folder = Folder::findOrFail($folderId);

        // Check if the user owns the folder
        if ($folder->user_id !== $user->id) {
            return $this->sendError('Unauthorized.', 401);
        }

        // Remove the blog from the folder
        $folder->blogs()->detach($request->blog_id);

        // If the folder is empty, delete it
        if ($folder->blogs()->count() === 0) {
            $folder->delete();
        }

        return $this->sendResponse([], 'Blog removed from folder successfully.');
    }
}
