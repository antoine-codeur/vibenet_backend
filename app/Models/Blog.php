<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Blog",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="My First Blog"),
 *     @OA\Property(property="description", type="string", example="This is the description of my first blog."),
 *     @OA\Property(property="owner_id", type="integer", example=1),
 * )
 */
class Blog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'owner_id'
    ];

    /**
     * Get the user that owns the blog.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
