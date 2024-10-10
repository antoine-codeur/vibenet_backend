<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     *     summary="Get all folders for the authenticated user",
     *     tags={"Folders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of folders retrieved successfully",
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
        $folders = Auth::user()->folders; // Assume the relationship is defined in the User model
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
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Folder")
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
        $request->validate(['name' => 'required|string']);
        $folder = Folder::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
        ]);
        return $this->sendResponse($folder, 'Folder created successfully.');
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
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function addBlogToFolder(Request $request, $folderId)
    {
        $request->validate(['blog_id' => 'required|integer']);
        $folder = Folder::findOrFail($folderId);
        $folder->blogs()->attach($request->blog_id);
        return $this->sendResponse([], 'Blog added to folder successfully.');
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
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function removeBlogFromFolder(Request $request, $folderId)
    {
        $request->validate(['blog_id' => 'required|integer']);
        $folder = Folder::findOrFail($folderId);
        $folder->blogs()->detach($request->blog_id);
        return $this->sendResponse([], 'Blog removed from folder successfully.');
    }
}