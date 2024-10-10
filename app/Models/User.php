<?php
  
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
  
/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", format="int64"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="profile_picture", type="string"),
 *         @OA\Property(property="bio", type="string"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     }
 * )
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'bio',
    ];    
  
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
    ];
  
    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define the relationship with the subscribed blogs.
     */
    public function subscribedBlogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_user');
    }

    /**
     * Define the relationship with folders.
     * Each user can have many folders.
     */
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
