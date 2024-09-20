<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User related operations"
 * )
 */
class ProfileController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     summary="Get the authenticated user's profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     )
     * )
     */
    public function show(Request $request)
    {
        // Return the authenticated user's profile as a JSON response
        return response()->json($request->user());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     summary="Update the authenticated user's profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="new_password123"),
     *                 @OA\Property(property="profile_picture", type="string", format="binary"),
     *                 @OA\Property(property="bio", type="string", example="Software Developer with 5 years of experience.")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=400, description="Invalid input", @OA\JsonContent(@OA\Property(property="error", type="string", example="Invalid data provided")))
     * )
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'profile_picture' => ['sometimes', 'image', 'max:2048'],
            'bio' => ['sometimes', 'string', 'max:500'],
        ]);

        // If password is provided, hash it before saving
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Check if a new image is provided
        if ($request->hasFile('profile_picture')) {
            // Delete the old profile picture if it exists
            $this->deleteProfilePicture($request); // Call the delete method

            // Store the new image and get the path
            $path = $request->file('profile_picture')->store('uploads/profile_pictures', 'public');
            $validatedData['profile_picture'] = $path;

            Log::info("File replaced with: $path");
        }

        // Update the user's data, ignoring any null values
        $user->update(array_filter($validatedData));

        return response()->json($user);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     summary="Delete the authenticated user's profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User profile deleted successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error in deleting the profile",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unable to delete user profile."
     *             )
     *         )
     *     )
     * )
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        try {
            // Delete the old profile picture
            $this->deleteProfilePicture($request);

            // Delete the user from the database
            $user->delete();

            return response()->json(['message' => 'User profile deleted successfully.']);
        } catch (\Exception $e) {
            // Return an error response if deletion fails
            return response()->json(['error' => 'Unable to delete user profile.'], 400);
        }
    }

    public function deleteProfilePicture(Request $request)
    {
        $user = $request->user();

        // Check if the user has a profile picture
        if ($user->profile_picture) {
            $oldImagePath = $user->profile_picture; // Use the stored path

            // Log the path being checked
            Log::info("Checking existence of profile picture: $oldImagePath");

            // Check if the file exists before trying to delete it
            if (Storage::disk('public')->exists($oldImagePath)) {
                if (Storage::disk('public')->delete($oldImagePath)) {
                    Log::info("Profile picture deleted: $oldImagePath");
                    // Update the user's profile_picture field to null after deletion
                    $user->update(['profile_picture' => null]);
                    return response()->json(['message' => 'Profile picture deleted successfully.']);
                } else {
                    return $this->sendError('Failed to delete profile picture.');
                }
            } else {
                return $this->sendError('Profile picture not found.');
            }
        }

        return $this->sendError('No profile picture to delete.');
    }

}