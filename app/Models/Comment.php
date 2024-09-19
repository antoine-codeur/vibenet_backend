<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     required={"id", "post_id", "user_id", "content"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="post_id", type="integer", example=2),
 *     @OA\Property(property="user_id", type="integer", example=3),
 *     @OA\Property(property="content", type="string", example="This is a comment."),
 *     @OA\Property(property="is_visible", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 * )
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'user_id', 'content', 'is_visible'
    ];

    /**
     * Get the post that owns the comment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
