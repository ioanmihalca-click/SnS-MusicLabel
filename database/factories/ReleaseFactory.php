<?php

namespace Database\Factories;

use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Release>
 */
class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'spotify_embed_code' => '<iframe src="https://open.spotify.com/embed/track/'
                .fake()->regexify('[A-Za-z0-9]{22}')
                .'" width="100%" height="352" frameborder="0"></iframe>',
        ];
    }
}
