<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'content' => $this->faker->sentence,
            'type' => 'text',
            'blog_id' => Blog::factory(),
            'owner_id' => User::factory(),
            'image_url' => null,
        ];
    }
}
