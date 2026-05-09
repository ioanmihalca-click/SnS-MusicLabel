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
        ];
    }
}
