<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Playlist>
 */
class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'spotify_embed_url' => '<iframe src="https://open.spotify.com/embed/playlist/'
                .fake()->regexify('[A-Za-z0-9]{22}')
                .'" width="100%" height="352" frameborder="0"></iframe>',
            'order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
