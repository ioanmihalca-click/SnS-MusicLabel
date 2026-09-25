<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'order' => fake()->numberBetween(1, 100),
            'spotify_url' => 'https://open.spotify.com/artist/'.fake()->regexify('[A-Za-z0-9]{22}'),
            'description' => fake()->paragraph(),
            'role' => fake()->randomElement(['DJ / producer', 'Duo', 'Producer']),
            'origin' => fake()->country(),
            'highlights' => [fake()->sentence(6), fake()->sentence(6)],
        ];
    }

    /**
     * As on the old site: a name, an order, a Spotify link and a description.
     */
    public function legacy(): static
    {
        return $this->state(fn () => [
            'role' => null,
            'origin' => null,
            'highlights' => null,
        ]);
    }
}
