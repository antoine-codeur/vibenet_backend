<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Folder",
 *     type="object",
 *     required={"id", "name", "user_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="My Folder"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-19T08:53:09.000000Z"),
 * )
 */
class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'folder_blog');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
