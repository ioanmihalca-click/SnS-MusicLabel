<?php

namespace Database\Factories;

use App\Models\Release;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'release_id' => Release::factory(),
            'position' => 1,
            'title' => Str::title(fake()->words(2, true)),
            'version' => null,
            'duration_seconds' => fake()->numberBetween(150, 420),
            'isrc' => null,
        ];
    }
}
