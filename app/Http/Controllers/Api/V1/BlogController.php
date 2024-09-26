<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Blog;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="owner", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="profile_picture", type="string")
     *             )
     *         ))
     *     )
     * )
     */
    public function index()
    {
        $blogs = Blog::with('owner:id,name,profile_picture')->get();
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description"},
     *                 @OA\Property(property="name", type="string", example="My First Blog"),
     *                 @OA\Property(property="description", type="string", example="This is my first blog."),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="logo", type="string", format="binary")
     *             )
     *         )
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
        // Check if the user already has a blog
        $existingBlog = Blog::where('owner_id', auth()->id())->first();
        
        if ($existingBlog) {
            return $this->sendError('User can only have one blog.', ['error' => 'User can only have one blog.']);
        }

        // Validate data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|max:2048', // Validate image
            'logo' => 'nullable|image|max:1024', // Validate logo
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Handle image and logo upload
        $imagePath = null;
        $logoPath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/blog_images', 'public');
        }
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('uploads/blog_logos', 'public');
        }

        // Create the blog
        $blog = Blog::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => auth()->id(), // Owner is the authenticated user
            'image' => $imagePath, // Save image path
            'logo' => $logoPath, // Save logo path
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
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="owner", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="profile_picture", type="string")
     *             )
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
    public function show($id)
    {
        $blog = Blog::with('owner:id,name,profile_picture')->find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        return $this->sendResponse($blog, 'Blog retrieved successfully.');
    }

    /**
     * @OA\Post(
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
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Updated Blog Name"),
     *                 @OA\Property(property="description", type="string", example="Updated description."),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="logo", type="string", format="binary")
     *             )
     *         )
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
    public function update(Request $request, $id)
    {
        // Trouver le blog
        $blog = Blog::find($id);
        if (!$blog) {
            return $this->sendError('Blog not found.');
        }

        // Validation des fichiers image et logo uniquement
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|max:2048', // Validation de l'image
            'logo' => 'nullable|image|max:1024', // Validation du logo
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Gestion du téléchargement de l'image
        if ($request->hasFile('image')) {
            $this->deleteBlogImage($blog->image); // Suppression de l'ancienne image si nécessaire
            $imagePath = $request->file('image')->store('uploads/blog_images', 'public');
            $blog->image = $imagePath; // Mise à jour du chemin de l'image
        }

        // Gestion du téléchargement du logo
        if ($request->hasFile('logo')) {
            $this->deleteBlogImage($blog->logo); // Suppression de l'ancien logo si nécessaire
            $logoPath = $request->file('logo')->store('uploads/blog_logos', 'public');
            $blog->logo = $logoPath; // Mise à jour du chemin du logo
        }

        // Mise à jour du nom et de la description si présents dans la requête
        if ($request->filled('name')) {
            $blog->name = $request->input('name');
        }

        if ($request->filled('description')) {
            $blog->description = $request->input('description');
        }

        // Sauvegarder les changements
        $blog->save();

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

        // Delete image and logo if they exist
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }
        if ($blog->logo) {
            Storage::disk('public')->delete($blog->logo);
        }

        $blog->delete();
        return $this->sendResponse([], 'Blog deleted successfully.');
    }

    /**
     * Helper function to delete an image or logo.
     */
    public function deleteBlogImage($oldImagePath)
    {
        if ($oldImagePath) {
            Log::info("Checking existence of image or logo: $oldImagePath");

            if (Storage::disk('public')->exists($oldImagePath)) {
                if (Storage::disk('public')->delete($oldImagePath)) {
                    Log::info("Image or logo deleted successfully: $oldImagePath");
                    return true;
                } else {
                    Log::error("Failed to delete image or logo: $oldImagePath");
                    return false;
                }
            } else {
                Log::warning("Image or logo not found: $oldImagePath");
                return false;
            }
        }

        Log::warning("No image or logo to delete.");
        return false;
    }

}
