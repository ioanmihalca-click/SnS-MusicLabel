<?php

namespace Database\Factories;

use App\Models\FeaturedTrack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeaturedTrack>
 */
class FeaturedTrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'artist_name' => fake()->name(),
            'spotify_track_url' => 'https://open.spotify.com/track/'.fake()->regexify('[A-Za-z0-9]{22}'),
            'cover_image' => null,
            'released_at' => now()->subDays(fake()->numberBetween(0, 60)),
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
