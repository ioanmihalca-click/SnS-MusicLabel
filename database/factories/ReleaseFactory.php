<?php

namespace Database\Factories;

use App\Enums\Genre;
use App\Enums\ReleaseFormat;
use App\Models\Release;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

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
            'released_at' => fake()->dateTimeBetween('-3 years', 'now'),
            'format' => ReleaseFormat::Single,
            'genre' => fake()->randomElement(Genre::cases()),
            'artist_display' => null,
            'spotify_url' => 'https://open.spotify.com/track/'.fake()->regexify('[A-Za-z0-9]{22}'),
            'smartlink_url' => null,
            'cover_image' => null,
            'is_featured' => false,
            'support' => [],
            'chart_position' => null,
            'chart_name' => null,
        ];
    }

    /**
     * Promoted in the homepage hero.
     */
    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /**
     * As converted from the old site: only a title, a description and the Spotify link.
     */
    public function legacy(): static
    {
        return $this->state(fn () => [
            'released_at' => null,
            'format' => null,
            'genre' => null,
            'support' => null,
        ]);
    }

    /**
     * With a tracklist numbered from 1.
     */
    public function withTracks(int $count = 2): static
    {
        return $this->has(
            Track::factory()
                ->count($count)
                ->sequence(fn (Sequence $sequence): array => ['position' => $sequence->index + 1]),
        );
    }
}
