<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     required={"id", "blog_id", "owner_id", "content"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="blog_id", type="integer", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=2),
 *     @OA\Property(property="content", type="string", example="This is a post content."),
 *     @OA\Property(property="image_url", type="string", example="/storage/images/example.jpg"),
 *     @OA\Property(property="type", type="string", example="image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 * )
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id', 'owner_id', 'content', 'image_url', 'type'
    ];

    /**
     * Get the blog that owns the post.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Get the user that owns the post.
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
