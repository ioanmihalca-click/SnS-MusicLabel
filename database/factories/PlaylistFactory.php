<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Playlist>
 */
class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'title' => Str::title(fake()->words(3, true)),
            'description' => implode(' · ', fake()->words(3)),
            'spotify_url' => 'https://open.spotify.com/playlist/'.fake()->regexify('[A-Za-z0-9]{22}'),
            'cover_image' => null,
            'order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    /**
     * As converted from the old site: only the Spotify link, order and status.
     */
    public function legacy(): static
    {
        return $this->state(fn () => [
            'title' => null,
            'description' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
