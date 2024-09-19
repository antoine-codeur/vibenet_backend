<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/explore",
     *     summary="Get all blogs",
     *     tags={"Blogs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of blogs",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Blog"))
     *     )
     * )
     */
    public function index()
    {
        $blogs = Blog::all();
        return $this->sendResponse($blogs, 'Blogs retrieved successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/blogs",
     *     summary="Create a new blog",
     *     tags={"Blogs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="My First Blog"),
     *             @OA\Property(property="description", type="string", example="This is my first blog."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Blog created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Blog")
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
    public function store(Request $request)
    {
        // Vérifiez si l'utilisateur a déjà un blog
        $existingBlog = Blog::where('owner_id', auth()->id())->first();
        
        if ($existingBlog) {
            return $this->sendError('User can only have one blog.', ['error' => 'User can only have one blog.']);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Création du blog
        $blog = Blog::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => auth()->id(), // Propriétaire est l'utilisateur connecté
        ]);

        return $this->sendResponse($blog, 'Blog created successfully.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/blogs/{id}",
     *     summary="Get a blog by ID",
     *     tags={"Blogs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Blog")
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
    public function show($id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        return $this->sendResponse($blog, 'Blog retrieved successfully.');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/blogs/{id}",
     *     summary="Update a blog",
     *     tags={"Blogs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Updated Blog Name"),
     *             @OA\Property(property="description", type="string", example="Updated description."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Blog")
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $blog = Blog::find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        $blog->update($request->all());
        return $this->sendResponse($blog, 'Blog updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/blogs/{id}",
     *     summary="Delete a blog",
     *     tags={"Blogs"},
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
    public function destroy($id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        $blog->delete();
        return $this->sendResponse([], 'Blog deleted successfully.');
    }
}
