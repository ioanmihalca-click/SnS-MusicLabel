<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_path' => 'photos/'.fake()->uuid().'.jpg',
        ];
    }
}
